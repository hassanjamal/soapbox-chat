<?php

namespace App\Http\Middleware;

use App\Models\Channel;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckWorkspaceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if( $request->has('workspace_id')){
            $userWorkspaces = Auth::user()->workspaces->pluck('id')->toArray();
            if (in_array(request('workspace_id'), $userWorkspaces)) {
                return $next($request);
            }
        }
        return response()->json(['message' => 'You are not authorised to access the workspace'],422   );
    }
}
