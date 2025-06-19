<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

	use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasApiTokens, Notifiable, SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'customers';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'is_active' => 1,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'name',
		'avatar',
        'phone',
		'document',
		'email',
		'cnpj',
		'perfil_acesso_id',
		'password',
        'birth_date',
        'accepted_term_of_users_at',
        'accepted_policy_privacy_at',
		'is_active',
		// settings
		'accept_newsletter',
		'notify_general',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at'				=> 'datetime',
		'account_verified_at'			=> 'datetime',
        'accepted_term_of_users_at'		=> 'datetime',
        'accepted_policy_privacy_at'	=> 'datetime',
		'is_active'						=> 'boolean',
		'accept_newsletter'				=> 'boolean',
		'birth_date'					=> 'date',
	];

	/**
	 * Avatar sizes
	 *
	 * @return object
	 */
	public function getAvatarSizes()
	{
		$return = [];

		$image = $this->getAttributeFromArray('avatar');

		if( $image )
		{
			// Remove storage/ from begin
			$image = preg_replace('/^storage\//', '', $image);

			// Get urls
			$return = url_image_cache($image);
		}

		return (object) $return;
	}

	/**
	 * Send the password reset notification.
	 *
	 * @param string $token
	 *
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		$notification = new ResetPassword($token);
		$type         = 'customers';

		$notification->createUrlUsing(function ($notifiable, $token) use ($type) {
			return url(config('app.url') . route('password.reset', [
					'type'  => $type,
					'token' => $token,
					'email' => $notifiable->email,
				], false));
		});

		$this->notify($notification);
	}

	/**
	 * Get activity logs
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function activity_logs()
	{
		return $this->hasMany(CustomerActivity::class, 'customer_id');
	}

    /**
	 * Get push to user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function push_to_user()
	{
		return $this->hasMany(PushUser::class, 'customer_id');
	}

	/**
	 * Get perfil
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function perfil()
	{
		return $this->belongsTo(PerfisAcesso::class, 'perfil_acesso_id');
	}

	/**
	 * Get preferencias
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function preferencias()
	{
		return $this->belongsToMany(Preferencias::class, 'customers_has_preferencias', 'customer_id', 'preferencia_id');
	}

    /**
     * Relacionamento muitos-para-muitos com Notification
     */
    public function notifications()
    {
        return $this->belongsToMany(Notifications::class, 'customer_has_notifications', 'customer_id', 'notification_id')
            ->withPivot(['read', 'deleted'])
            ->withTimestamps();
    }

    /**
     * Relacionamento muitos-para-muitos com treinamento
     */
    public function treinamentos()
    {
        return $this->belongsToMany(Treinamento::class, 'customer_has_treinamento', 'customer_id', 'treinamento_id')
            ->withPivot(['read', 'favorited'])
            ->withTimestamps();
    }
}