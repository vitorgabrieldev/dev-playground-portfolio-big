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

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

	use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasApiTokens, Notifiable, SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

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
		'email',
		'password',
		'avatar',
		'custom_data',
		'is_active',
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
		'email_verified_at' => 'datetime',
		'custom_data'       => 'object',
		'is_active'         => 'boolean',
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
	 * List permissions key
	 *
	 * @return array
	 */
	public function myPermissions()
	{
		$perms = [];

		foreach( $this->roles as $role )
		{
			foreach( $role->permissions as $permission )
			{
				if( !in_array($permission->key, $perms) )
				{
					$perms[] = $permission->key;
				}
			}
		}

		return $perms;
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
		$type         = 'users';

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
	 * Get roles
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_has_roles', 'user_id', 'role_id');
	}

	/**
	 * Get activity logs
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function activity_logs()
	{
		return $this->hasMany(Log::class, 'user_id');
	}
}

