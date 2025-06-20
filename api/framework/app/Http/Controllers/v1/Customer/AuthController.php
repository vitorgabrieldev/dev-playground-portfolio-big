<?php

namespace App\Http\Controllers\v1\Customer;

use App\Enum\Document;
use App\Exceptions\GeneralException;
use App\Http\Resources\Customer\CustomerResource;
use App\Lib\Zenvia;
use App\Mail\Admin\AlertDeleteAccount;
use App\Mail\Customer\AccessCodeMail;
use App\Mail\Admin\AlertNewAccount;
use Illuminate\Auth\Events\Registered;
use App\Mail\Customer\VerifyAccountMail;
use App\Models\PerfisAcesso;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

/**
 * Class AuthController
 *
 * @resource Authentication
 * @package  App\Http\Controllers\v1\Customer
 */
class AuthController extends Controller
{

	/**
	 * Auth guard
	 *
	 * @var string
	 */
	protected $guard = 'customers';

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
	 * Login external - sem verificacao de segurança
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
	public function loginExternal(Request $request)
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
		$this->log('account', $user->name, 'Realizou o login de forma externa', $user);

		return response()->json([
			'token_type'   => 'Bearer',
			'access_token' => $token,
		]);
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
	 * @bodyParam  email string required|email email
	 * @bodyParam  password string sometimes|nullable|string|min:6 password
	 * @bodyParam  token_name string required|string|max:191 static(My&nbsp;Computer) Client app name
	 * @bodyParam  name string required|string|max:50 name
	 * @bodyParam  avatar string sometimes|nullable|string string
	 *
	 * @response {
	 * "token": "eyJ0eXAiOiJ..."
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function login(Request $request)
	{
		//  * @bodyParam  login_external_id string sometimes|nullable|string|max:191 string
		//  * @bodyParam  network_external string sometimes|nullable|string|max:10 string Default: `login-pass`.<br>Available values: `facebook` `google` `apple`

		$this->validate($request, [
			$this->username() 	=> 'sometimes|nullable|email',
			'avatar'        	=> 'sometimes|nullable|string',
			'password'        	=> 'sometimes|nullable|string|min:6',
			// 'login_external_id'	=> 'sometimes|nullable|string|max:191',
			// 'network_external'  => 'sometimes|nullable|string|max:10',
			'token_name'      	=> 'required|string|max:191',
		]);

		$origin = /*$request->input('network_external') ?? */ 'login-pass';
		$email = $request->input($this->username());

		// validate
		if($origin == "login-pass")
		{
			if(!$request->input('password'))
				throw GeneralException::free("Campo de senha é obrigatório.");

			if(!$request->input($this->username()))
				throw GeneralException::free("Campo de email é obrigatório.");
		}
		// else
		// {
		// 	if(!$request->input('login_external_id'))
		// 		throw GeneralException::free("Id do usuário na rede obrigatório.");
		// }

		if($email)
		{
			$user_exist = Customer::where("email",$email)->whereNull('email_verified_at')->first();

			// verifica se a conta nao fo validada, e email para validar
			if($user_exist)
			{
				// send email
				$token = Str::random(60);
				EmailVerification::create([
					'customer_id' => $user_exist->id,
					'token' => $token,
				]);

				Mail::to($email)->send(new VerifyAccountMail($user_exist, $token));

				return response()->json(['message' => 'Um e-mail com o link de validação do seu cadastro foi enviado. Por favor, verifique sua caixa de entrada.']);
			}
		}

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

		if($origin == "login-pass")
		{
			$credentials = [
				$this->username() => $request->input($this->username()),
				'password'        => $request->input('password'),
				'is_active'       => 1,
			];

			// If invalid credentials
			if( !Auth::guard('web_' . $this->guard)->attempt($credentials, false) )
			{
				$check_deleted = Customer::where("email",$email)->onlyTrashed()->first();
				$check_inactive = Customer::where("email",$email)->where("is_active",false)->first();

				// verifica se a conta nao foi excluida ou esta ativa
				if($check_deleted)
				{
					return response()->json(['message' => __('auth.deleted')], 422);
				}
				else
				{
					RateLimiter::hit($throttleKey);

                    if($check_inactive)
                        return response()->json(['message' => __('auth.inactive')], 422);
                    else
                        throw GeneralException::make('invalid_login', 422);
				}
			}

			// Get user
			/** @var Customer $user */
			$user = Auth::guard('web_' . $this->guard)->user();

			// verifica se a conta esta verificada
			if (is_null($user->email_verified_at))
			{
				Auth::guard('web_' . $this->guard)->logout();
				throw GeneralException::make('not_verified');
			}
		}
		// else
		// {
		// 	$credentials = [
		// 		$this->username() 	=> $request->input($this->username()),
		// 		'login_external_id'	=> $request->input('login_external_id'),
		// 		'network_external'	=> $origin,
		// 		'is_active'       	=> 1,
		// 	];

		// 	// If invalid credentials
		// 	if( !Auth::guard('socialnetwork_' . $this->guard)->attempt($credentials, false) )
		// 	{
		// 		// se o usuario nao tiver conta via rede social, cria e ja loga.
		// 		// Create
		// 		$new_user = new Customer($request->only(['name', 'email', 'document','phone', 'avatar','login_external_id']));
		// 		$new_user->uuid = uuid();
		// 		$new_user->is_active = true;
		// 		$new_user->email_verified_at = now();
		// 		$new_user->network_external = $origin;
		// 		$new_user->save();

		// 		return $this->login($request);
		// 	}

		// 	// Get user
		// 	/** @var Customer $user */
		// 	$user = Auth::guard('socialnetwork_' . $this->guard)->user();
		// }

		RateLimiter::clear($throttleKey);

		$encrypt = [
			'id'			=> $user->id,
			'email'			=> $user->email,
			'phone'			=> $user->phone,
			'token_name'	=> $request->input('token_name')
		];

		// Save log
		$this->log('account', $user->name, 'Realizou o login', $user);

		return response()->json([
			'token'			=> Crypt::encrypt($encrypt),
		]);
	}

	/**
	 * Logout
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 */
	public function logout()
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
	 * Send code
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam token string required|string static(eyJpdiI6IklpSEdzdkVLRE05SjFySHNCRGZ...) token generate in `Check login` request
	 * @bodyParam send_by string required|string static(sms) Available values: `email` `sms`
	 *
	 * @response {
	 * "message": "Código enviado..."
	 * }
	 * 
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function sendCode(Request $request)
	{
		$this->validate($request, [
			'token'   => 'required|string',
			'send_by' => 'required|string|in:email,sms',
		]);

		$send_by = $request->input('send_by');

		try
		{
			$decrypted = Crypt::decrypt($request->input('token'));
		}
		catch( DecryptException $e )
		{
			throw GeneralException::free('Token inválido');
		}

		$codeCacheKey = 'customer-login-code-' . $decrypted['id'];

		if( Cache::has($codeCacheKey) )
		{
			$code = Cache::get($codeCacheKey);
		}
		else
		{
			$code = faker()->regexify('[0-9]{6}');

			// Save on cache
			Cache::put($codeCacheKey, $code, now()->addMinutes(30));
		}

		if( is_environment_local() )
		{
			clock('Cache key "' . $codeCacheKey . '" code "' . $code . '"');
		}

		if( $send_by === 'email' )
		{
			$message = 'Código enviado para o e-mail ' . Str::mask($decrypted['email'], '*', 1, strpos($decrypted['email'], '@') - 1);

			Mail::to($decrypted['email'])->send(new AccessCodeMail($code, 'login'));
		}
		else
		{
			// Validate phone
			if( !$decrypted['phone'] )
			{
				throw GeneralException::free('Usuário sem telefone cadastrado.');
			}

			$message = 'Código enviado para o celular ' . Str::mask($decrypted['phone'], '*', 6, 4);

			(new Zenvia())->sendSMS('55' . only_numbers($decrypted['phone']), 'Grupo AIZ, seu código de acesso é ' . $code);
		}

		return response()->json([
			'message' => $message,
			// TODO apenas para teste, remover
			'code'    => $code,
		]);
	}

	/**
	 * Validate code
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam  token string required|string static(eyJpdiI6IklpSEdzdkVLRE05SjFySHNCRGZ...) token generate in `Check login` request
	 * @bodyParam  code numeric required|numeric static(004399) Code received by email or sms
	 *
	 * @response {
	 * "token_type": "Bearer",
	 * "access_token": "eyJ0eXAiOiJ..."
	 * }
	 * 
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function validateCode(Request $request)
	{
		$this->validate($request, [
			'token' => 'required|string',
			'code'  => 'required|digits:6|regex:/^[0-9]+$/',
		], [
			'code.digits' => 'O código informado é inválido!',
			'code.regex'  => 'O código informado é inválido!',
		]);

		try
		{
			$decrypted = Crypt::decrypt($request->input('token'));
		}
		catch( DecryptException $e )
		{
			$this->logout();
			throw GeneralException::free('Token inválido');
		}

		$codeCacheKey = 'customer-login-code-' . $decrypted['id'];

		// Check has generated
		if( !Cache::has($codeCacheKey) )
		{
			$this->logout();
			throw GeneralException::free('Código expirado!');
		}

		// Validate
		if( $request->input('code') !== Cache::get($codeCacheKey) )
		{
			$this->logout();
			throw GeneralException::free('O código informado é inválido!');
		}

		// Additional param
		$decrypted['code_validated'] = true;

		// Delete cache
		Cache::forget($codeCacheKey);

		// Login user
		Auth::guard('web_' . $this->guard)->loginUsingId($decrypted['id'], false);

		/** @var Customer $user */
		$user = Auth::guard('web_' . $this->guard)->user();

		if( !$user )
		{
			throw GeneralException::free('Usuário não encontrado.');
		}
		else
		{
			// valida a conta
			if($user->account_verified_at == null)
			{
				$user->account_verified_at = now();
				$user->save();
			}
		}

		// Create a token
		$token = $user->createToken($decrypted['token_name']);

		// Save log
		$this->log('account', $user->name, 'Realizou o login', $user);

		return response()->json([
			'token_type'   => 'Bearer',
			'access_token' => $token->plainTextToken,
		]);
	}

	/**
	 * Create Account
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam  email string required|string|email|max:100 safeEmail
	 * @bodyParam  name string required|string|max:50 name
	 * @bodyParam  document string sometimes|nullable|string|max:20 string static(000.999-777-11)
	 * @bodyParam  cnpj string sometimes|nullable|string|max:20 string static(16.159.151/0001-27)
	 * @bodyParam  token_name string sometimes|nullable|string|max:191 static(My&nbsp;Computer) Client app name
	 * @bodyParam  avatar file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
     * @bodyParam  phone string required|string|email|max:18 phone static((43) 91111-2222)
	 * @bodyParam  accepted_term_of_users_at string required|string static(2025-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` now
	 * @bodyParam  accepted_policy_privacy_at string required|string static(2025-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` now
	 * @bodyParam  password string sometimes|nullable|string|min:6 static(my_new_password) New password
	 * @bodyParam  password_confirmation string sometimes|nullable|string|min:6 static(my_new_password) Confirmation, must equal `password`
	 * @bodyParam  birth_date string string static(1999-07-29) ISO 8601 `Y-m-d` data de nascimento
	 * @bodyParam  preferencias[] array sometimes|nullable|string|max:191 uuid
	 * @bodyParam  perfil_acesso uuid required|uuid uuid
	 * @bodyParam  accept_newsletter boolean boolean boolean Default true
	 * @bodyParam  notify_general boolean boolean boolean Default true
	 *
	 * @return CustomerResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function store(Request $request)
	{
		//  * @bodyParam  login_external_id string sometimes|nullable|string|max:191 string
		//  * @bodyParam  network_external string sometimes|nullable|string|max:10 string Default: `login-pass`.<br>Available values: `facebook` `google` `apple`
		$this->validate($request, [
			'email'                         => ['required', 'string', 'email',
			Rule::unique((new Customer())->getTable(), 'email')->where(function ($query) {
				return $query->whereNull('deleted_at');
			})],
			'name'          		        => 'required|string|max:50|min_words:2',
			'document'    		    		=> 'sometimes|nullable|string|max:20',
			'cnpj'	    		    		=> 'sometimes|nullable|string|max:20',
			// 'login_external_id'	            => 'sometimes|nullable|string|max:191',
			// 'network_external'              => 'sometimes|nullable|string|max:10',
			'token_name'      	            => 'sometimes|nullable|string|max:191',
			'avatar'						=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
            'phone'                         => 'required|string|phone|max:18',
            'accepted_term_of_users_at'     => 'required|string|date',
            'accepted_policy_privacy_at'    => 'required|string|date',
			'password'                      => 'sometimes|nullable|string|min:6',
			'password_confirmation'         => 'sometimes|nullable|string|min:6|same:password',
			'birth_date'					=> 'sometimes|date_format:Y-m-d',
			'preferencias'					=> 'sometimes|array|min:1',
			'preferencias.*'				=> 'sometimes|string|max:191',
			'perfil_acesso'					=> 'required|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'accept_newsletter'				=> 'sometimes|required|boolean',
			'notify_general'				=> 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		$origin = /*$request->input('network_external') ??*/ 'login-pass';
		$password = $request->input('password') ?? null;
		$email = $request->input('email');

		try
		{
			$user_exist = Customer::where("email",$email)->whereNull('email_verified_at')->first();

			// verifica se a conta nao fo validada, e email para validar
			if($user_exist)
			{
				// send email
				$token = Str::random(60);
				EmailVerification::create([
					'customer_id' => $user_exist->id,
					'token' => $token,
				]);

				Mail::to($email)->send(new VerifyAccountMail($user_exist, $token));

				return response()->json(['message' => 'Um e-mail com o link de validação do seu cadastro foi enviado. Por favor, verifique sua caixa de entrada.']);
			}

            if($request->has('document'))
            {
                if(identificarDocumento($request->input('document')) == Document::CPF)
                {
                    // valida documento
                    if(!validarCPF($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CPF válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }
                else
                {
                    // valida documento
                    if(!validarCNPJ($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }

                if(Customer::where("document",$request->input('document'))->exists())
                {
                    return response()->json(['document' => 'Já existe uma conta com este documento. Para prosseguir com a criação da conta por favor adicione um outro documento. Caso persista, entre em contato com o suporte.'],422);
                }
            }

			if($request->has('cnpj'))
			{
				if(identificarDocumento($request->input('cnpj')) == Document::DESCONHECIDO)
				{
					return response()->json(['cnpj' => 'Tipo de documento inválido. Para prosseguir com a criação da conta por favor adicione um documento válido. Caso persista, entre em contato com o suporte.'],422);
				}
				else
				if(identificarDocumento($request->input('cnpj')) == Document::CNPJ)
				{
					// valida documento
					if(!validarCNPJ($request->input('cnpj')))
					{
						return response()->json(['cnpj' => 'CNPJ inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
					}
				}
			}

			// Create
			$new_user = new Customer($request->only([
				'name',
				'phone',
				'document',
				'cnpj',
				'email',
				'avatar',
				'accepted_term_of_users_at',
				'accepted_policy_privacy_at',
				'birth_date',
				'notify_general',
				'accept_newsletter',
			]));

			$new_user->uuid = uuid();
            // $new_user->network_external = $origin;
			$new_user->is_active = true;

			if($request->has('perfil_acesso'))
			{
				$perfil = PerfisAcesso::where("uuid", $request->input('perfil_acesso'))->first(["id"]) ?? null;
				if(!$perfil)
				{
					throw ValidationException::withMessages(['perfil_acesso' => __('Perfil não encontrado. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}

				$new_user->perfil_acesso_id = $perfil->id;
			}

			if($origin == 'login-pass')
			{
				$new_user->password = Hash::make($password);
			}
			// else
			// {
			// 	$new_user->email_verified_at = now();
			// 	$new_user->login_external_id = $request->input('login_external_id');
			// }
			$new_user->save();

			if($request->has('preferencias'))
			{
				$preferencias = Preferencias::whereIn('uuid', collect($request->input('preferencias'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

				if( !count($preferencias) )
				{
					throw ValidationException::withMessages(['preferencias' => __('Preferencia selecionada inválida. Tente novamente ou entre em contato com o suporte.')]);
				}

				$new_user->preferencias()->sync($preferencias);
			}

			$new_user->save();

			DB::commit();

			// listen event
			event(new Registered($new_user));

			// se for via rede social, ja loga
			// if($origin !== 'login-pass')
			// {
			// 	return $this->login($request);
			// }
			// else
			// {
				// send email
				$token = Str::random(60);
				EmailVerification::create([
					'customer_id' => $new_user->id,
					'token' => $token,
				]);

				Mail::to($email)->send(new VerifyAccountMail($new_user, $token));
			// }

			$encrypt = [
				'id'			=> $new_user->id,
				'email'			=> $new_user->email,
				'phone'			=> $new_user->phone,
				'token_name'	=> $request->input('token_name')
			];

			// alerta email p/ admin, criacao de conta
			Mail::to(explode(',', config('notifications.create_account')))->queue(new AlertNewAccount($new_user));

		}
		catch( Throwable $e )
		{
			DB::rollBack();
			throw $e;
		}

		return response()->json([
			'token'			=> Crypt::encrypt($encrypt),
		]);
	}

	/**
	 * Verify token send by email
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam  token string required|string|max:191 string
	 *
	 * @response {
	 * "message": "message successfully"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
    public function verifyEmail(Request $request)
    {
		$this->validate($request, [
			'token'          		=> 'required|string|max:191',
		]);

		DB::beginTransaction();

		try
		{
			$token = $request->input('token');

			$verification = (new EmailVerification())->where('token', $token)->first();

			if(!$verification)
			{
				return response()->json(['message' => 'E-mail já validado.']);
			}

			$user = $verification->customer;
			$user->email_verified_at = now();
			$user->save();

			$verification->delete();

			DB::commit();

			return response()->json(['message' => 'E-mail verificado com sucesso.']);
		}
		catch (\Throwable $e)
		{
			DB::rollBack();
			throw $e;
		}
    }

	/**
	 * Resend email verification account
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
	 *
	 * @bodyParam  email string required|string|email|max:100 safeEmail
	 *
	 * @response {
	 * "message": "message successfully"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
    public function resendVerificationEmail(Request $request)
    {
		$this->validate($request, [
			'email' => 'required|email'
		]);

		try
		{
			$user = Customer::where('email', $request->email)->firstOrFail();

			// send email
			$token = Str::random(60);
			EmailVerification::create([
				'customer_id' => $user->id,
				'token' => $token,
			]);

			Mail::to($user->email)->send(new VerifyAccountMail($user, $token));

			return response()->json(['message' => 'E-mail para verificação re-enviado com sucesso.']);
		}
		catch (\Throwable $th)
		{
			throw ValidationException::withMessages(['email' => 'E-mail não encontrado. Tente novamente.']);
		}
    }

	/**
	 * Update user
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string sometimes|nullable|string|max:191 string
	 * @bodyParam  document string sometimes|nullable|string|max:20 string static(000.999-777-11)
	 * @bodyParam  cnpj string sometimes|nullable|string|max:20 string static(16.159.151/0001-27)
     * @bodyParam  phone string required|string|email|max:18 phone static((43) 91111-2222)
	 * @bodyParam  is_active boolean sometimes|nullable|boolean boolean
	 * @bodyParam  avatar file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
     * @bodyParam  birth_date date sometimes|nullable|date data de aniversario
	 * @bodyParam  preferencias[] array required|string|max:191 uuid
	 * @bodyParam  perfil_acesso uuid required|uuid uuid
	 * @bodyParam  accept_newsletter boolean boolean boolean Default true
     *
	 * @return CustomerResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		// Get current user
		$user = $this->user();

		$this->validate($request, [
			'name'        		 	=> 'sometimes|nullable|string|max:191',
            'phone'       		 	=> 'sometimes|nullable|string|phone|max:18',
            'document'    		    => 'sometimes|nullable|string|max:20',
			'cnpj'					=> 'sometimes|nullable|string|max:20',
			'is_active'   		 	=> 'sometimes|nullable|boolean',
			'avatar'      		 	=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
            'birth_date'            => 'sometimes|nullable|date',
			'preferencias'			=> 'sometimes|array|min:1',
			'preferencias.*'		=> 'sometimes|nullable|string|max:191',
			'perfil_acesso'			=> 'sometimes|nullable|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'accept_newsletter'		=> 'sometimes|nullable|boolean',
		]);

		$user_old = clone $user;

		DB::beginTransaction();

		try
		{
            if($request->has('document'))
            {
                if(identificarDocumento($request->input('document')) == Document::CPF)
                {
                    // valida documento
                    if(!validarCPF($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CPF válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }
                else
                {
                    // valida documento
                    if(!validarCNPJ($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CPF válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }

                if(Customer::where("document",$request->input('document'))->where('id',"!=",$user->id)->exists())
                {
                    return response()->json(['document' => 'Já existe uma conta com este documento. Para prosseguir com a criação da conta por favor adicione um outro documento. Caso persista, entre em contato com o suporte.'],422);
                }
            }

			if($request->has('preferencias'))
			{
				$preferencias = Preferencias::whereIn('uuid', collect($request->input('preferencias'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

				if( !count($preferencias) )
				{
					throw ValidationException::withMessages(['preferencias' => __('Preferencia selecionada inválida. Tente novamente ou entre em contato com o suporte.')]);
				}

				$user->preferencias()->sync($preferencias);
			}

			// Update
			$user->fill($request->only([
                'name',
				'is_active',
                'phone',
                'document',
				'cnpj',
				'birth_date',
				'accept_newsletter'
			]));

			if($request->has('perfil_acesso'))
			{
				$perfil = PerfisAcesso::where("uuid", $request->input('perfil_acesso'))->first(["id"]) ?? null;
				if(!$perfil)
				{
					throw ValidationException::withMessages(['perfil_acesso' => __('Perfil não encontrado. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}

				$user->perfil_acesso_id = $perfil->id;
			}

			if($request->has('avatar'))
			{
				$file_name = $user->uuid . '_' . uuid() . '.jpg';
				$file_path = storage_path('app/public/avatar/' . $file_name);
				$file_url  = 'storage/avatar/' . $file_name;

				// Save image
				Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);
		
				// Update
				$user->avatar = $file_url;
			}

			$user->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();
			$this->log(__FUNCTION__, $user_old->name, $e->getMessage(), $user, $user_old);
			throw $e;
		}

		if( $user->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $user_old->name, 'Editou o perfil', $user, $user_old);
		}

		return new CustomerResource($user);
	}

	/**
	 * Get the authenticated user
	 *
	 * @return CustomerResource
	 */
	public function show()
	{
		// Get current user
		$user = $this->user();

		$user->load(['perfil', 'preferencias']);

		return new CustomerResource($user);
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
	 * Delete account
	 *
	 * @param Request $request
	 *
	 * @bodyParam  password string sometimes|nullable|string|min:6 static(my_password) my password
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 */
	public function deleteAccount(Request $request)
	{
		// Get current user
		$user = $this->user();

		// Receber a senha do request
		$password = $request->input('password');

		if (Hash::check($password, $user->password))
		{
			// Delete access tokens
			$user->tokens()->delete();

			$user->delete();

			// alerta email p/ admin, remocao de conta
			Mail::to(explode(',', config('notifications.delete_account')))->queue(new AlertDeleteAccount($user));

			return response(null, 204);
		}
		else
		{
			// Senha incorreta
			return response()->json(['message' => 'Senha incorreta'], 401);
		}
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
	 * settings notify
	 *
	 * @param Request $request
	 *
	 * @bodyParamnotify_general boolean boolean boolean
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
	 * @throws ValidationException
	 */
	public function settingsNotify(Request $request)
	{
		$this->validate($request, [
			'notify_general' => 'sometimes|nullable|boolean',
		]);

		// Get current user
		$user = $this->user();

		// Update
		$user->update($request->only([
			'notify_general',
		]));

		// Save log
		$this->log('account', $user->name, 'Atualizou sua configuração', $user);

		return new CustomerResource($user);
	}

	/**
	 * Change avatar
	 *
	 * @param Request $request
	 *
	 * @bodyParam avatar file required|mimes:jpeg,png|max:4mb file
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
			'avatar' => 'required|mimes:jpeg,png|max:4000',
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

		$user = Customer::isActive()->where('email', '=', $email)->first();

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
            'type'                  => 'required',
			'token'                 => 'required',
			'email'                 => 'required|email',
			'password'              => 'required|string|min:6',
			'password_confirmation' => 'required|string|min:6|same:password',
		]);

        $this->guard = $request->input('type');

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
