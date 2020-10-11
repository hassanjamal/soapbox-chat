<?php

namespace App\Http\Middleware;

use App\Models\Channel;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckChannelAccess
{
    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('channel_id')) {
            $userChannels = Auth::user()->channels->pluck('id')->toArray();
            if (in_array(request('channel_id'), $userChannels)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You are not authorised to access the channel'], 422);
    }
}
