<?php

/**
 * Created By: JISHNU T K
 * Date: 2024/07/20
 * Time: 22:45:01
 * Description: MessageController.php
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;

use App\Models\Message;
use App\Models\MessageRead;

use App\Events\MessageSent;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class MessageController extends ApiBaseController
{
    /**
     * Show Product Based Chat List
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function showProductMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:chats,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $messages = Message::where('chat_id', $request->input('chat_id'))->get();
            return $this->sendResponse($messages, 'Message List');
        }
    }

    /**
     * Send Message
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function storeProductMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'file_url' => 'nullable|url',
            'chat_id' => 'required|exists:chats,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $message = Message::create([
                'chat_id' => $request->input('chat_id'),
                'user_id' => $request->input('user_id'),
                'message' => $request->input('message'),
                'file_url' => $request->input('file_url')
            ]);
            broadcast(new MessageSent($message))->toOthers();
            return $this->sendResponse($message, 'Store Message');
        }
    }

    /**
     * Read Message
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function readMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:messages,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $messageReads = MessageRead::create([
                'message_id' => $request->input('message_id'),
                'user_id' => $request->input('message_id'),
                'read_at' => Carbon::now()
            ]);
            return $this->sendResponse($messageReads, 'Message Read');
        }
    }

    /**
     * Delete Message
     *
     * @param Request $request
     *
     * @return [type]
     */
    public function deleteMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:messages,id'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid data', $validator->errors()->toArray());
        } else {
            $message = Message::where('message_id', $request->input('message_id'))->delete();
            return $this->sendResponse($message, 'Message Deleted Successfully');
        }
    }
}
