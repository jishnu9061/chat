<?php

/**
 * Created By: JISHNU T K
 * Date: 2024/07/20
 * Time: 22:43:12
 * Description: ChatController.php
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends ApiBaseController
{
    /**
     * Show the Product Chat List
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function showProductChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $chats = Chat::where('product_id', $request->input('product_id'))->get();
            return $this->sendResponse($chats, 'Chat List');
        }
    }

    /**
     * Create Product Chat List  Or Show Older Conversation
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function storeProductChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $chat = Chat::where('product_id', $request->input('product_id'))
                ->where('user_id', $request->input('user_id'))
                ->first();

            if (!$chat) {
                $product = Product::find($request->input('product_id'));
                $chat = Chat::create([
                    'product_id' => $request->input('product_id'),
                    'user_id' => $request->input('user_id'),
                    'enlisted_user_id' => $product->user_id
                ]);
            }

            $chatWithMessages = DB::table(Chat::getTableName() . ' as c')
                ->Join(Message::getTableName() . ' as m', 'm.chat_id', '=', 'c.id')
                ->where('c.id', $chat->id)
                ->select('c.*', 'm.id as message_id', 'm.message', 'm.user_id as message_user_id', 'm.file_url', 'm.created_at as message_created_at')
                ->get();
            $chatList = [];
            foreach ($chatWithMessages as $data) {
                $chatList[] = [
                    'id' => $data->id,
                    'message_id' => $data->message_id,
                    'message' => $data->message,
                    'user_id' => $data->message_user_id,
                    'file_url' => $data->file_url,
                    'created_at' => $data->message_created_at
                ];
            }
            return $this->sendResponse($chatList, 'Chat Messages');
        }
    }
}
