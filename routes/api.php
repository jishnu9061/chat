<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);

Route::middleware(
    ['auth:api']
    )->group(function () {
    // Chat routes
    Route::get('products/{product_id}/chats', [ChatController::class, 'showProductChats']);
    Route::post('products/{product_id}/chats', [ChatController::class, 'storeProductChat']);

    // Message routes
    Route::get('chats/{chat_id}/messages', [MessageController::class, 'showChatMessages']);
    Route::post('chats/{chat_id}/messages', [MessageController::class, 'storeChatMessage']);

    // Additional message actions
    Route::post('messages/{message_id}/read', [MessageController::class, 'readMessage']);
    Route::delete('messages/{message_id}', [MessageController::class, 'deleteMessage']);
    Route::post('chats/{chat_id}/read-all', [MessageController::class, 'readAllMessages']);
});
