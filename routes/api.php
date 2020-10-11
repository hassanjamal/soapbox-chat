<?php

use App\Http\Controllers\ChannelActivities;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChannelUserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WorkSpaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->group(function() {

    Route::post('/workspaces', [WorkSpaceController::class, 'store'])
         ->name('workspace.store');

    Route::post('/channel/invite', [ChannelUserController::class, 'store'])
         ->name('channels.users.store')
         ->middleware('workspace-access','channel-access');

    Route::post('/channels', [ChannelController::class, 'store'])
         ->name('channel.store')
         ->middleware('workspace-access');

    Route::get('/channels', [ChannelController::class, 'index'])
         ->name('channel.index')
         ->middleware('workspace-access');

    Route::get('/channel/activities', ChannelActivities::class)
         ->name('channel.activity')
         ->middleware('workspace-access', 'channel-access');

    Route::post('/messages', [MessageController::class , 'store'])
         ->name('message.create')
         ->middleware('workspace-access', 'channel-access');

    Route::get('/messages', [MessageController::class , 'index'])
         ->name('message.index')
         ->middleware('workspace-access', 'channel-access');
});


