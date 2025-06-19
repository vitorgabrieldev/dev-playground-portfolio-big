<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriasTreinamento extends Model
{
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'categorias_treinamento';

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
        'order',
		'is_active',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'is_active' => 'boolean',
	];

	/**
	 * Get treinamentos
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function treinamentos()
	{
		return $this->hasMany(Treinamento::class, 'categoria_treinamento_id');
	}
}