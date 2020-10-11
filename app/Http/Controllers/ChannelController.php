<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * @param Request $request
     */
    public function index (Request $request)
    {
        $workspace = Workspace::with('channels')->find(request('workspace_id'));
        return response($workspace->channels, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:channels,name',
            'workspace_id' => 'required'
        ]);
        $channel = Channel::create([
            'name' => request('name'),
            'description' => request('description'),
            'workspace_id' => request('workspace_id')
        ]);
        if($channel){
            return (response(['message' => 'Channel created successfully'], 200));
        }
    }

}
