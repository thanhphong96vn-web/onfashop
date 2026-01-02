<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel for admin chat system
Broadcast::channel('chat.{chatThreadId}', function ($user, $chatThreadId) {
    $chatThread = \App\Models\ChatThread::find($chatThreadId);
    return $chatThread && $chatThread->user_id == $user->id;
});

// Channel for conversation system (seller chat)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && (
        $conversation->sender_id == $user->id || 
        $conversation->receiver_id == $user->id
    );
});
