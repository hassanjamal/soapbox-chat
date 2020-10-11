<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChannelActivities extends Controller
{
    public function __invoke(Request $request)
    {
        // TODO - Implement Activities
        $activities = [
            1 => 'First Activity',
            2 => 'Second Activity'
        ];
        return response($activities, 200);
    }
}
