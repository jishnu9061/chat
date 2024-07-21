<?php

/**
 * Created By: JISHNU T K
 * Date: 2024/07/20
 * Time: 22:45:01
 * Description: MessageController.php
 */

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Chat;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\ChatMessageStoreRequest;
use App\Http\Controllers\Api\ApiBaseController;

class MessageController extends ApiBaseController
{

    private const MESSAGE_IMAGE_PATH = 'messages';

    public function showChatMessages($chatId)
    {
        $loggedUserId = getUser()->id;
        if (!$this->validateChatAccess($chatId, $loggedUserId)) {
            throw new UnauthorizedException();
        }

        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($message) use ($loggedUserId) {
                $message->is_sender = $message->user_id === $loggedUserId;
                return $message;
            });

        return $this->makeSuccessResponse($messages->toArray(), 'Message List');
    }

    /**
     * Send Message
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function storeChatMessage(ChatMessageStoreRequest $request, $chatId)
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

    public function readMessage(Request $request, $messageId)
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

    public function deleteMessage(Request $request, $messageId)
    {
        if (!$this->validateMessageAccess($messageId, getUser()->id)) {
            throw new UnauthorizedException();
        }

        $message = Message::findOrFail($messageId);
        $message->delete();

        return $this->makeSuccessResponse([], 'Message Deleted Successfully');
    }

    private function validateMessageAccess($messageId, $userId)
    {
        return Chat::join('messages', 'chats.id', '=', 'messages.chat_id')
            ->where('messages.id', $messageId)
            ->select('chats.user_id', 'chats.enlisted_user_id')
            ->where(function ($query) use ($userId) {
                $query->where('chats.user_id', $userId)
                    ->orWhere('chats.enlisted_user_id', $userId);
            });
    }

    private function validateChatAccess($chatId, $userId)
    {
        return Chat::where('id', $chatId)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('enlisted_user_id', $userId);
            })->exists();
    }
}
