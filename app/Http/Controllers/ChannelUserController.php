<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelUserController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);
        $workspaceAccess = Workspace::with([
            'users' => function($q) {
                $q->where('users.id', request('user_id'));
            }
        ])->find($request->get('workspace_id'));

        if ($workspaceAccess && $workspaceAccess->users->count()) {
            $channel = Channel::find(request('channel_id'));
            $channel->users()->attach(request('user_id'));

            return (response(['message' => 'User added to channel successfully'], 200));
        }
        return response(['message' => 'Something went wrong !! User may not have access to current workspace'],422);

    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
