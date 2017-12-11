<?php

namespace ctf0\Lingo\Http\Middleware;

use ctf0\Lingo\Lingo;

class Authenticate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return \Illuminate\Http\Response|null
     */
    public function handle($request, $next)
    {
        return Lingo::check($request) ? $next($request) : abort(403);
    }
}