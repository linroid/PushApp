<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;

class Authenticate {
	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard $auth
	 */
	public function __construct(Guard $auth) {
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		if ($this->auth->guest()) {
			if ($request->ajax()) {
				return response('Unauthorized.', 401);
			}
			else {
				if ($request->has('package')) {
                    Session::put('package', $request->get('package'));
				}
				return redirect()->guest('/');
			}
		}

		return $next($request);
	}
}
