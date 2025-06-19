<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Notifications extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'notifications';

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
		'text',
		'descricao',
		'video',
		'capa',
		'titulo_button',
		'link_button',
		'data_envio',
		'send_push',
		'is_active',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'is_active' => 'boolean',
		'data_envio' => 'datetime',
	];

	/**
	 * capa sizes
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
     * Relacionamento muitos-para-muitos com customer
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_has_notifications', 'notification_id', 'customer_id')
            ->withPivot(['read', 'deleted'])
            ->withTimestamps();
    }
}
