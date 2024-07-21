<?php

declare(strict_types=1);

/**
 * Created By: JISHNU T K
 * Date: 2024/07/20
 * Time: 22:43:12
 * Description: ChatController.php
 */

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Api\ApiBaseController;
use App\Events\ChatCreated;

class ChatController extends ApiBaseController
{
    /**
     * Show the list of chats for a specific product.
     *
     * @param int $productId
     * @return JsonResponse
     */
    public function showProductChats(int $productId): JsonResponse
    {
        $loggedUserId = getUser()->id;

        // Retrieve chats for the specific product
        $chats = Chat::where('product_id', $productId)
            ->where(function ($query) use ($loggedUserId) {
                $query->where('user_id', $loggedUserId)
                    ->orWhere('enlisted_user_id', $loggedUserId);
            })
            ->get();

        return $this->makeSuccessResponse($chats->toArray(), 'Chat List');
    }

    /**
     * Create a new chat for a product or show an existing conversation.
     *
     * @param int $productId
     * @return JsonResponse
     * @throws UnauthorizedException
     */
    public function storeProductChat(int $productId): JsonResponse
    {
        $userId = getUser()->id;

        // Validate that the user is not the owner of the product
        if (Product::where('user_id', $userId)->where('id', $productId)->exists()) {
            throw new UnauthorizedException();
        }

        // Retrieve or create chat
        $chat = Chat::firstOrCreate(
            ['product_id' => $productId, 'user_id' => $userId],
            ['enlisted_user_id' => Product::findOrFail($productId)->user_id]
        );

        broadcast(new ChatCreated($chat))->toOthers();

        return $this->makeSuccessResponse($chat->toArray(), 'Chat Response');
    }
}
