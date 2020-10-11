<?php

namespace App\Http\Controllers;

use App\Events\MessageSentToChannel;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class MessageController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $message = Message::where('channel_id', request('channel_id'))->get();

        return response($message, 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
        ]);
        $message = Message::create([
            'content' => request('content'),
            'channel_id' => request('channel_id'),
            'user_id' => Auth::user()->id
        ]);
        if ($message) {
            MessageSentToChannel::dispatch($message);

            return (response(['message' => 'Message added to channel successfully'], 200));
        }

        return response(['message' => 'Something went wrong !! User may not have access to current channel'], 422);
    }
}
