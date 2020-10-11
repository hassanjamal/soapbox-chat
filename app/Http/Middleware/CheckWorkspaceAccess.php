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
            $workspaceAccess = Workspace::with([
                'users' => function($q) {
                    $q->where('users.id', Auth::user()->id);
                }
            ])->find($request->get('workspace_id'));

            if ($workspaceAccess && $workspaceAccess->users->count()){
                return $next($request);
            }
        }
        return response()->json(['message' => 'You are not authorised to access the workspace'],422   );
    }
}
