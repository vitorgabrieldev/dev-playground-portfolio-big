<?php

namespace App\Models;

class SystemLog extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'system_logs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'user_type',
		'user_id',
		'message',
		'level',
		'context',
		'ip',
		'user_agent',
		'url',
		'method',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'context' => 'object',
	];

	/**
	 * Color
	 *
	 * @return string
	 */
	public function getColor()
	{
		$level = $this->getAttributeFromArray('level');

		switch( $level )
		{
			case 'INFO':
				$return = '#1a73d1';
				break;

			case 'NOTICE':
				$return = '#50a756';
				break;

			case 'WARNING':
				$return = '#ff900f';
				break;

			case 'ERROR':
				$return = '#ff5720';
				break;

			case 'CRITICAL':
				$return = '#f34336';
				break;

			case 'ALERT':
				$return = '#d92c3f';
				break;

			case 'EMERGENCY':
				$return = '#b31f1b';
				break;

			default:
				$return = '#8a8a8a';
				break;
		}

		return $return;
	}

	/**
	 * Get user owner
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function user()
	{
		return $this->morphTo('user')->withTrashed();
	}
}
