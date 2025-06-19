<?php

namespace App\Http\Middleware;

use Closure;

class DisableOnJsonRequest
{

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// IF wants json
		if( $request->wantsJson() )
		{
			abort(404);
		}

		return $next($request);
	}
}
