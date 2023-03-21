<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserContactController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::post('/register', [UserController::class,'register']);
Route::post('/updateProfile', [UserController::class,'updateProfile']);
Route::post('/userProfile', [UserController::class,'userProfile']);
Route::post('/deleteAccount', [UserController::class,'deleteAccount']);
Route::post('/sendFeedback', [UserController::class,'sendFeedback']);
Route::post('/loginMobile', [UserController::class,'loginMobile']);
Route::post('/userLogout', [UserController::class,'userLogout']);

Route::post('/addUserContacts', [UserContactController::class,'addUserContacts']);
Route::post('/userContactList', [UserContactController::class,'userContactList']);
Route::post('/updateContactStatus', [UserContactController::class,'updateContactStatus']);
Route::post('/userContactListByStatus', [UserContactController::class,'userContactListByStatus']);

Route::post('/sendVoiceMessage', [UserContactController::class,'sendVoiceMessage']);
Route::post('/chatHistoryList', [UserContactController::class,'chatHistoryList']);
Route::post('/deleteUserFromChatHistory', [UserContactController::class,'deleteUserFromChatHistory']);
Route::post('/updateChatHistoryStatus', [UserContactController::class,'updateChatHistoryStatus']);

Route::post('/updateSeenStatusFromChatHistory', [UserContactController::class,'updateSeenStatusFromChatHistory']);
Route::post('/accountDelete', [UserController::class,'accountDelete']);
//Route::post('pushNotification/{id}', [UserController::class,'pushNotification']);
Route::post('pushNotification/', [UserController::class,'pushNotification']);


Route::post('/downloadFile/', [UserController::class,'downloadFile']);
