<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkSpaceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:workspaces,name',
        ]);
        $workspace = Workspace::create([
            'name' => request('name')
        ]);
        if($workspace){
            return (response(['message' => 'Workspace created successfully'], 200));
        }

    }

}
