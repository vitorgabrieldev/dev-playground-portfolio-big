<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\GeneralException;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

/**
 * Class AuthController
 *
 * @resource Authentication
 * @package  App\Http\Controllers\v1\Admin
 */
class AuthController extends Controller
{

	/**
	 * Auth guard
	 *
	 * @var string
	 */
	protected $guard = 'users';

	/**
	 * Attempts limit
	 *
	 * @var int
	 */
	protected $maxAttempts = 5;

	/**
	 * Minutes attempts
	 *
	 * @var int
	 */
	protected $decayMinutes = 1;

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function username()
	{
		return 'email';
	}

	/**
	 * Get the rate limiting throttle key for the request.
	 *
	 * @return string
	 */
	public function throttleKey($request)
	{
		return Str::lower($request->input($this->username())) . '|' . $request->ip();
	}

	/**
	 * Login
	 *
	 * 5 login attempts can be performed every 1 minute.
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam email string required|email email
	 * @bodyParam password string required|string|min:6 password
	 * @bodyParam token_name string required|string|max:191 static(My&nbsp;Computer) Client app name
	 *
	 * @response {
	 * "token_type": "Bearer",
	 * "access_token": "eyJ0eXAiOiJ..."
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function login(Request $request)
	{
		$this->validate($request, [
			$this->username() => 'required|email',
			'password'        => 'required|string|min:6',
			'token_name'      => 'required|string|max:191',
		]);

		$throttleKey = $this->throttleKey($request);

		if( RateLimiter::tooManyAttempts($throttleKey, 5) )
		{
			event(new Lockout($request));

			$seconds = RateLimiter::availableIn($throttleKey);

			throw ValidationException::withMessages([
				'email' => trans('auth.throttle', [
					'seconds' => $seconds,
					'minutes' => ceil($seconds / 60),
				]),
			])->status(Response::HTTP_TOO_MANY_REQUESTS);
		}

		$credentials = [
			$this->username() => $request->input($this->username()),
			'password'        => $request->input('password'),
			'is_active'       => 1,
		];

		// If invalid credentials
		if( !Auth::guard('web_' . $this->guard)->attempt($credentials, false) )
		{
			RateLimiter::hit($throttleKey);

			throw GeneralException::make('invalid_login');
		}

		RateLimiter::clear($throttleKey);

		// Get user
		/** @var User $user */
		$user = Auth::guard('web_' . $this->guard)->user();

		// Create a token
		$token = $user->createToken($request->input('token_name'))->plainTextToken;

		// Save log
		$this->log('account', $user->name, 'Realizou o login', $user);

		return response()->json([
			'token_type'   => 'Bearer',
			'access_token' => $token,
		]);
	}

	/**
	 * Logout
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 */
	public function logout(Request $request)
	{
		// Get current user
		$user = $this->user();

		// Revoke current token
		$user->currentAccessToken()->delete();

		// Save log
		$this->log('account', $user->name, 'Se deslogou', $user);

		return response(null, 204);
	}

	/**
	 * Get the authenticated user
	 *
	 * @return UserResource
	 */
	public function show()
	{
		// Get current user
		$user = $this->user();

		// Load relations
		$user->load('roles.permissions');

		return new UserResource($user);
	}

	/**
	 * Get all tokens
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function tokens(Request $request)
	{
		// Get current user
		$user = $this->user();

		$currentTokenId = (int) $user->currentAccessToken()->id;

		$items = $user->tokens()->select(['id', 'name', 'abilities', 'last_used_at', 'updated_at'])->orderByRaw('FIELD(id, ' . $currentTokenId . ') desc')->orderBy('last_used_at', 'desc')->get();

		foreach( $items as $item )
		{
			$item->current = $currentTokenId === (int) $item->id;
		}

		return response()->json(['data' => $items]);
	}

	/**
	 * Delete token
	 *
	 * @param $tokenId
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 */
	public function tokenDelete($tokenId)
	{
		// Get current user
		$user = $this->user();

		// Busca o token
		$token = $user->tokens()->where('id', '=', $tokenId)->first();

		if( $token )
		{
			// Revoke token
			$token->delete();

			// Save log
			$this->log('account', $token->name, 'Deletou um token de acesso', $token);
		}

		return response(null, 204);
	}

	/**
	 * Change password
	 *
	 * @param Request $request
	 *
	 * @bodyParam password string required|string|min:6 static(my_current_password) Current password
	 * @bodyParam password_new string required|string|min:6 static(my_new_password) New password
	 * @bodyParam password_new_confirmation string required|string|min:6 static(my_new_password) Confirmation, must equal `password_new`
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 * @throws ValidationException
	 */
	public function changePassword(Request $request)
	{
		$this->validate($request, [
			'password'                  => 'required|string|min:6',
			'password_new'              => 'required|string|min:6',
			'password_new_confirmation' => 'required|string|min:6|same:password_new',
		]);

		// Get current user
		$user = $this->user();

		// Check current password
		if( !Hash::check($request->input('password'), $user->password) )
		{
			throw ValidationException::withMessages(['password' => __('validation.invalid_current_password')]);
		}

		// Update
		$user->update([
			'password' => Hash::make($request->input('password_new')),
		]);

		// Save log
		$this->log('account', $user->name, 'Alterou sua senha', $user);

		return response(null, 204);
	}

	/**
	 * Change avatar
	 *
	 * @param Request $request
	 *
	 * @bodyParam avatar file required|mimes:jpeg,png|max:2mb file
	 *
	 * @response {
	 * "file_url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function changeAvatar(Request $request)
	{
		$this->validate($request, [
			'avatar' => 'required|mimes:jpeg,png|max:2000',
		]);

		// Get current user
		$user = $this->user();

		$file_name = $user->uuid . '_' . uuid() . '.jpg';
		$file_path = storage_path('app/public/avatar/' . $file_name);
		$file_url  = 'storage/avatar/' . $file_name;

		// Save image
		Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);

		// Update
		$user->update([
			'avatar' => $file_url,
		]);

		// Save log
		$this->log('account', $user->name, 'Alterou seu avatar', $user);

		return response()->json(['file_url' => asset($user->avatar)]);
	}

	/**
	 * Password recovery
	 *
	 * Send password reset link do user
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam email string required|email email
	 *
	 * @response {
	 * "message": "Success message"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function passwordRecovery(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email',
		]);

		$email = $request->input('email');

		$user = User::isActive()->where('email', '=', $email)->first();

		if( !$user )
		{
			throw ValidationException::withMessages(['email' => __('passwords.user')]);
		}

		$broker = Password::broker($this->guard);

		// We will send the password reset link to this user. Once we have attempted
		// to send the link, we will examine the response then see the message we
		// need to show to the user. Finally, we'll send out a proper response.
		$response = $broker->sendResetLink([
			'email' => $email,
		]);

		if( $response !== Password::RESET_LINK_SENT )
		{
			throw ValidationException::withMessages(['email' => trans($response)]);
		}

		return response()->json(['message' => __('passwords.sent')]);
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function passwordRecoveryReset(Request $request)
	{
		$this->validate($request, [
			'token'                 => 'required',
			'email'                 => 'required|email',
			'password'              => 'required|string|min:6',
			'password_confirmation' => 'required|string|min:6|same:password',
		]);

		$broker = Password::broker($this->guard);

		$status = $broker->reset(array_merge($request->only('password', 'password_confirmation', 'token'), ['email' => $request->input('email')]), function ($user, $password) use ($request) {
			$user->forceFill([
				'password' => Hash::make($password),
			])->save();

			$user->setRememberToken(Str::random(60));

			event(new PasswordReset($user));
		});

		if( $status === Password::PASSWORD_RESET )
		{
			return response(null, 204);
		}

		throw ValidationException::withMessages(['email' => __($status)]);
	}
}
