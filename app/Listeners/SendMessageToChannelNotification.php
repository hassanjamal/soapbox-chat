<?php

namespace App\Listeners;

use App\Events\MessageSentToChannel;
use App\Models\Channel;
use App\Notifications\NewMessageToChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SendMessageToChannelNotification implements ShouldQueue
{
    use Queueable;
    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     * @param MessageSentToChannel $event
     * @return void
     */
    public function handle(MessageSentToChannel $event)
    {
        $users = Channel::with('users')->find($event->message->channel_id)->users;

        Notification::send($users->except(Auth::user()->id), new NewMessageToChannel($event->message));
    }
}
