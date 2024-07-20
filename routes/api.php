<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;

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

Route::get('show-chats', [ChatController::class, 'showProductChat']);
Route::post('store-chats', [ChatController::class, 'storeProductChat']);
Route::get('show-message', [MessageController::class, 'showProductMessage']);
Route::post('store-message', [MessageController::class, 'storeProductMessage']);

Route::post('read-message', [MessageController::class, 'readMessage']);
Route::post('delete-message', [MessageController::class, 'deleteMessage']);
