<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Resource extends JsonResource
{

	/**
	 * Show id
	 *
	 * @var bool
	 */
	protected $showId = false;

	/**
	 * Show relationships if they were loaded
	 *
	 * <code>
	 * $array = [
	 *    'foo' => BarResource::class,
	 *    'bar' => FooResource::class,
	 *    'relationMorphToModel' => [
	 *        'morphtoTypeFooModel::class' => BarResource::class,
	 *        'morphtoTypeBarModel::class' => FooResource::class,
	 *    ],
	 * ];
	 * </code>
	 *
	 * @var array
	 */
	protected $showRelations = [];

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArray($request)
	{
		$toArrayComplement = $this->toArrayComplement($request);

		// Prepare complements
		$complements = $this->mergeWhen(count($toArrayComplement), function () use ($toArrayComplement) {
			$return = [];

			foreach( $toArrayComplement as $field_key => $field_value )
			{
				if( is_numeric($field_key) )
				{
					if( $field_value instanceof MergeValue or $field_value instanceof MissingValue )
					{
						$return[] = $field_value;
					}
					elseif( $this->resource->{$field_value} instanceof Carbon )
					{
						$value = $this->{$field_value} ? $this->{$field_value}->toAtomString() : null;

						// Has date cast
						if( $this->resource->hasCast($field_value, ['date', 'datetime', 'immutable_date', 'immutable_datetime', 'custom_datetime', 'immutable_custom_datetime']) )
						{
							$castType = $this->resource->getCasts()[$field_value];

							// Set date format
							if( is_string($castType) and str_contains($castType, ':') )
							{
								$segments = explode(':', $castType, 2);

								if( isset($segments[1]) and $segments[1] !== '' )
								{
									$value = $this->{$field_value} ? $this->{$field_value}->format($segments[1]) : null;
								}
							}
						}

						$return[$field_value] = $this->when($this->resource->hasAttribute($field_value), $value);
					}
					else
					{
						$return[$field_value] = $this->when($this->resource->hasAttribute($field_value), $this->{$field_value});
					}
				}
				else
				{
					$return[$field_key] = $field_value;
				}
			}

			return $return;
		});

		// Predefined data
		$return = [
			'id'   => $this->when($this->showId and $this->resource->hasAttribute('id'), $this->id),
			'uuid' => $this->when($this->resource->hasAttribute('uuid'), $this->uuid),
			'name' => $this->when($this->resource->hasAttribute('name'), $this->name),
		];

		// Merge complements
		foreach( $complements as $complement )
		{
			foreach( $complement as $complement_key => $complement_data )
			{
				$return[$complement_key] = $complement_data;
			}
		}

		// Predefined data
		$return['order']      = $this->when($this->resource->hasAttribute('order'), function () {
			return $this->order;
		});
		$return['is_system']  = $this->when($this->resource->hasAttribute('is_system'), $this->is_system);
		$return['is_active']  = $this->when($this->resource->hasAttribute('is_active'), $this->is_active);
		$return['created_at'] = $this->when($this->resource->hasAttribute('created_at'), $this->created_at ? $this->created_at->toAtomString() : null);
		$return['updated_at'] = $this->when($this->resource->hasAttribute('updated_at'), $this->updated_at ? $this->updated_at->toAtomString() : null);
		$return['deleted_at'] = $this->when($this->resource->hasAttribute('deleted_at'), $this->deleted_at ? $this->deleted_at->toAtomString() : null);

		// Prepare relations
		$relations = $this->mergeWhen(count($this->showRelations), function () {
			$return = [];

			foreach( $this->showRelations as $relationship => $resource )
			{
				if( $this->resource->relationLoaded($relationship) )
				{
					// Array, is morphTo map relation
					if( is_array($resource) )
					{
						foreach( $resource as $morph_type_model => $morph_type_resource )
						{
							// Check relation is model
							if( $this->resource->{$relationship} instanceof $morph_type_model )
							{
								$return[$relationship] = new $morph_type_resource($this->resource->{$relationship});

								break;
							}
						}
					}
					else
					{
						if( $this->resource->{$relationship} instanceof Collection )
						{
							$return[$relationship] = $resource::collection($this->resource->{$relationship});
						}
						else
						{
							$return[$relationship] = new $resource($this->resource->{$relationship});
						}
					}
				}
			}

			return $return;
		});

		// Merge relations
		foreach( $relations as $relation )
		{
			foreach( $relation as $relation_name => $relation_data )
			{
				$return[$relation_name] = $relation_data;
			}
		}

		return $return;
	}

	/**
	 * Complement to transform the resource into an array.
	 *
	 * @param $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [];
	}
}
