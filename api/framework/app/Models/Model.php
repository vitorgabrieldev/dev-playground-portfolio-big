<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as ModelEloquent;

class Model extends ModelEloquent
{

	use HasFactory;

	/**
	 * The number of models to return for pagination.
	 *
	 * @var int
	 */
	protected $perPage = 20;

	/**
	 * Check if has the attribute
	 *
	 * @param $attr
	 *
	 * @return bool
	 */
	public function hasAttribute($attr)
	{
		return array_key_exists($attr, $this->attributes);
	}

	/**
	 * Prepare a date for array / JSON serialization.
	 *
	 * @param DateTimeInterface $date
	 *
	 * @return string
	 */
	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format(DateTimeInterface::ATOM);
	}

	/**
	 * Scope a query to only system items.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeIsSystem($query)
	{
		return $query->where($this->qualifyColumn('is_system'), '=', 1);
	}

	/**
	 * Scope a query to only active items.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeIsActive($query)
	{
		return $query->where($this->qualifyColumn('is_active'), '=', 1);
	}

	/**
	 * Scope a query to only approved items.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeIsApproved($query)
	{
		return $query->where($this->qualifyColumn('is_approved'), '=', 1);
	}

	/**
	 * Scope a query to only featured items.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeIsFeatured($query)
	{
		return $query->where($this->qualifyColumn('is_featured'), '=', 1);
	}

	/**
	 * Get logs
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function logs()
	{
		return $this->morphMany(Log::class, 'log');
	}
}
