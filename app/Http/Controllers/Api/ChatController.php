<?php

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
use Illuminate\Support\Facades\DB;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Api\ApiBaseController;

class ChatController extends ApiBaseController
{
    /**
     * Show Product Chat List
     *
     * @param [type] $productId
     *
     * @return [type]
     */
    public function showProductChats($productId): JsonResponse
    {
        $loggedUserId = getUser()->id;
        // Retrieve chats for the specific product
        $chats = Chat::where('product_id', $productId)->where(function ($query) use ($loggedUserId) {
            $query->where('chats.user_id', $loggedUserId)
                ->orWhere('chats.enlisted_user_id', $loggedUserId);
        })->get();
        return $this->makeSuccessResponse($chats->toArray(), 'Chat List');
    }

    /**
     * Create Product Chat List  Or Show Older Conversation
     *
     */
    public function storeProductChat($productId): JsonResponse
    {
        $userId = getUser()->id;


        // To do validate the  product user can't add chat for that product
        if (Product::where('user_id', $userId)->exists()) {
            throw new UnauthorizedException();
        }

        // Retrieve or create chat
        $chat = Chat::firstOrCreate(
            ['product_id' => $productId, 'user_id' => $userId],
            ['enlisted_user_id' => Product::find($productId)->user_id]
        );
        return $this->makeSuccessResponse($chat->toArray(), 'Chat Response');
    }
}
