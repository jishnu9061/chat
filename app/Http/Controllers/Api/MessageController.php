<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Chat;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\ChatMessageStoreRequest;
use App\Http\Controllers\Api\ApiBaseController;

class MessageController extends ApiBaseController
{
    private const MESSAGE_IMAGE_PATH = 'messages';

    /**
     * Show paginated chat messages
     *
     * @param int $chatId
     * @param Request $request
     * @return JsonResponse
     */
    public function showChatMessages(int $chatId, Request $request): JsonResponse
    {
        $loggedUserId = getUser()->id;
        if (!$this->validateChatAccess($chatId, $loggedUserId)) {
            throw new UnauthorizedException();
        }

        $perPage = (int) $request->input('per_page', 10); // Default to 10 messages per page

        // Fetch paginated messages
        $paginator = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Enhance messages with additional data
        $messages = $paginator->getCollection()->map(function (Message $message) use ($loggedUserId) {
            $message->is_sender = $message->user_id === $loggedUserId;
            return $message;
        });

        // Prepare response data
        $response = [
            'data' => $messages,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'previous_page_url' => $paginator->previousPageUrl(),
            ],
        ];

        return $this->makeSuccessResponse($response, 'Message List');
    }

    /**
     * Store a new chat message
     *
     * @param ChatMessageStoreRequest $request
     * @param int $chatId
     * @return JsonResponse
     */
    public function storeChatMessage(ChatMessageStoreRequest $request, int $chatId): JsonResponse
    {
        $loggedUserId = getUser()->id;

        if (!$this->validateChatAccess($chatId, $loggedUserId)) {
            throw new UnauthorizedException();
        }

        $chat = Chat::findOrFail($chatId);

        $now = Carbon::now();
        $userReadAt = $chat->user_id === $loggedUserId ? $now : null;
        $enlistedUserReadAt = $chat->enlisted_user_id === $loggedUserId ? $now : null;

        $fileUrl = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = Storage::disk('public')->put(self::MESSAGE_IMAGE_PATH . '/' . $chatId, $file);
            $fileUrl = Storage::disk('public')->url($filePath);
        }

        $message = Message::create([
            'chat_id' => $chatId,
            'user_id' => $loggedUserId,
            'message' => $request->input('message'),
            'file_url' => $fileUrl,
            'user_read_at' => $userReadAt,
            'enlisted_user_read_at' => $enlistedUserReadAt
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return $this->makeSuccessResponse($message->toArray(), 'Message stored successfully');
    }

    /**
     * Mark a message as read
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function readMessage(Request $request, int $messageId): JsonResponse
    {
        $loggedUserId = getUser()->id;

        if (!$this->validateMessageAccess($messageId, $loggedUserId)) {
            throw new UnauthorizedException();
        }

        $message = Message::findOrFail($messageId);
        $chat = Chat::findOrFail($message->chat_id);

        $now = Carbon::now();
        if ($chat->user_id === $loggedUserId) {
            $message->user_read_at = $now;
        }
        if ($chat->enlisted_user_id === $loggedUserId) {
            $message->enlisted_user_read_at = $now;
        }

        $message->save();

        return $this->makeSuccessResponse($message->toArray(), 'Message Read');
    }

    /**
     * Delete a message
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function deleteMessage(Request $request, int $messageId): JsonResponse
    {
        if (!$this->validateMessageAccess($messageId, getUser()->id)) {
            throw new UnauthorizedException();
        }

        $message = Message::findOrFail($messageId);
        $message->delete();

        return $this->makeSuccessResponse([], 'Message Deleted Successfully');
    }

    /**
     * Validate if the user has access to the message
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    private function validateMessageAccess(int $messageId, int $userId): bool
    {
        return Chat::join('messages', 'chats.id', '=', 'messages.chat_id')
            ->where('messages.id', $messageId)
            ->select('chats.user_id', 'chats.enlisted_user_id')
            ->where(function ($query) use ($userId) {
                $query->where('chats.user_id', $userId)
                    ->orWhere('chats.enlisted_user_id', $userId);
            })->exists();
    }

    /**
     * Validate if the user has access to the chat
     *
     * @param int $chatId
     * @param int $userId
     * @return bool
     */
    private function validateChatAccess(int $chatId, int $userId): bool
    {
        return Chat::where('id', $chatId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('enlisted_user_id', $userId);
            })->exists();
    }

    public function readAllMessages(Request $request, int $chatId): JsonResponse
    {
        $loggedUserId = getUser()->id;

        if (!$this->validateChatAccess($chatId, $loggedUserId)) {
            throw new UnauthorizedException();
        }

        $chat = Chat::findOrFail($chatId);

        $now = Carbon::now();
        if ($chat->user_id === $loggedUserId) {
            Message::where('chat_id', $chatId)
                ->whereNull('user_read_at')
                ->update(['user_read_at' => $now]);
        }
        if ($chat->enlisted_user_id === $loggedUserId) {
            Message::where('chat_id', $chatId)
                ->whereNull('enlisted_user_read_at')
                ->update(['enlisted_user_read_at' => $now]);
        }

        return $this->makeSuccessResponse([], 'All Messages Read');
    }
}
