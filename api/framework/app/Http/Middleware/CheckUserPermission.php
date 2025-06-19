<?php

namespace App\Http\Middleware;

use App\Exceptions\UserPermissionException;
use Closure;

class CheckUserPermission
{

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure                 $next
	 * @param                          ...$permissions
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$permissions)
	{
		// If you do not have permission
		if( !user_has_permission($permissions) )
		{
			throw UserPermissionException::make($permissions);
		}

		return $next($request);
	}
}
