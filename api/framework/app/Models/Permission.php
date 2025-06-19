<?php

namespace App\Models;

class Permission extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'permissions';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'order' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'key',
		'name',
		'group',
		'order',
	];

	/**
	 * Get roles
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
	}
}
