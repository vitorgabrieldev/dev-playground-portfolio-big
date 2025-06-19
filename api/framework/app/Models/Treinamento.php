<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Treinamento extends Model
{
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'treinamento';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'is_active' => 1,
		'destaque' => 1,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'name',
		'capa',
		'video',
		'duracao',
		'descricao',
		'categoria_treinamento_id',
        'order',
		'destaque',
		'is_active',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'destaque' => 'boolean',
		'is_active' => 'boolean',
	];

	/**
	 * Capa sizes
	 *
	 * @return object
	 */
	public function getCapaSizes()
	{
		$return = [];

		$image = $this->getAttributeFromArray('capa');

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
	 * Get categoria
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function categoria()
	{
		return $this->belongsTo(CategoriasTreinamento::class, 'categoria_treinamento_id');
	}

    /**
     * Relacionamento muitos-para-muitos com customer
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_has_treinamento', 'treinamento_id', 'customer_id')
            ->withPivot(['read', 'favorited'])
            ->withTimestamps();
    }
}