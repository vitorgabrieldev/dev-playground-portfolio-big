<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\RepositoryException;
use App\Models\Log;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Controller extends BaseController
{

	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	protected $getExport = false;

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	protected $fieldSortable = [];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [];

	/**
	 * Default ordering
	 *
	 * @var array
	 */
	protected $orderByDefault = [
		[
			'name' => 'id',
			'sort' => 'asc',
		],
	];

	/**
	 * Limit results
	 *
	 * @var integer
	 */
	protected $limit = 20;

	/**
	 * Max limit results
	 *
	 * @var integer
	 */
	protected $limitMax = 100;

	/**
	 * Get logged user
	 *
	 * @return mixed|User;
	 */
	protected function user()
	{
		return current_user_admin();
	}

	protected function log($action, $item_name, $message, $item = null, $item_old = null)
	{
		$request = request();

		$qs = $request->getQueryString();

		// Get current user
		$user = $this->user();

		$log = new Log([
			'uuid'       => uuid(),
			'log_name'   => Str::limit($item_name, 191),
			'message'    => Str::limit($message, 191),
			'action'     => $action,
			'old_data'   => $item_old,
			'new_data'   => $item,
			'ip'         => $request->ip(),
			'user_agent' => Str::ascii($request->userAgent()),
			'url'        => urldecode($request->getPathInfo() . ($qs !== null ? '?' . $qs : '')),
			'method'     => $request->getMethod(),
		]);

		if( $item instanceof Model )
		{
			$log->log()->associate($item);
		}

		$user->activity_logs()->save($log);
	}

	/**
	 * Prepares the fields for sorting
	 *
	 * @param null $orderBy
	 * @param null $fieldSortable
	 *
	 * @return array|null
	 */
	protected function getOrderBy($orderBy = null, $fieldSortable = null)
	{
		if( empty($orderBy) )
		{
			return $this->orderByDefault;
		}

		if($fieldSortable == null)
		{
			$fieldSortable = $this->fieldSortable;
		}

		$return = [];

		// Get fields
		$orderByFields = explode('|', $orderBy);

		if( $orderByFields )
		{
			foreach( $orderByFields as $orderByField )
			{
				// Get column and direction
				$fieldExp  = explode(':', $orderByField);
				$fieldName = $fieldExp[0];

				// Validate field
				if( !in_array($fieldName, $fieldSortable) )
				{
					throw RepositoryException::make('orderBy', ['field' => $fieldName, 'fields' => "'" . implode("', '", $this->fieldSortable) . "'"]);
				}

				$return[] = [
					'name' => $fieldName,
					'sort' => strtolower($fieldExp[1] ?? 'asc') == 'asc' ? 'asc' : 'desc',
				];
			}
		}

		return $return;
	}

	/**
	 * Check if exist orderBy
	 *
	 * @param array $orderByList
	 * @param mixed ...$search
	 *
	 * @return bool
	 */
	protected function hasOrderBy(array $orderByList, ...$search)
	{
		foreach( $orderByList as $orderBy )
		{
			if( in_array($orderBy['name'], $search) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Prepares limit results
	 *
	 * @param integer $limit
	 *
	 * @return int|null
	 */
	protected function getLimit($limit = null)
	{
		$limit = (int) $limit;

		if( empty($limit) )
		{
			return $this->limit;
		}

		if( $limit > $this->limitMax )
		{
			throw RepositoryException::make('limit', ['limit_max' => $this->limitMax]);
		}

		return $limit;
	}

	/**
	 * Export data
	 *
	 * @param $columns
	 * @param $items
	 * @param $prefix
	 * @param $log_message
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	protected function exportData($columns, $items, $prefix, $log_message)
	{
		$request = request();

		// Get current user
		$user = $this->user();

		$file_name = $user->uuid . '_' . $prefix . '_' . now()->format('Y-m-d-H\h-i\m-s\s') . '.xlsx';
		$file_path = storage_path('app/public/export/' . $file_name);
		$file_url  = 'storage/export/' . $file_name;

		// Log
		$logItem = $request->query();

		// Create excel
		$spreadsheet = new Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();

		// Columns
		foreach( $columns as $key => $column )
		{
			$sheet->getCellByColumnAndRow($key + 1, 1)->setValue($column['name']);
		}

		if( count($items) )
		{
			$i = 2;

			foreach( $items as $item )
			{
				foreach( $columns as $key => $column )
				{
					$keyColumn = $key + 1;
					$value     = $column['value'] instanceof Closure ? $column['value']($item) : $item->{$column['value']};

					if( isset($column['type']) )
					{
						if( $column['type'] === 'date' )
						{
							if( !is_null($value) )
							{
								$sheet->getCellByColumnAndRow($keyColumn, $i)
									  ->setValue(\PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($value))
									  ->getStyle()
									  ->getNumberFormat()
									  ->setFormatCode('dd/mm/yyyy');
							}
							else
							{
								$sheet->getCellByColumnAndRow($keyColumn, $i)->setValue($value);
							}
						}
						elseif( $column['type'] === 'datetime' )
						{
							if( !is_null($value) )
							{
								$sheet->getCellByColumnAndRow($keyColumn, $i)
									  ->setValue(\PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($value))
									  ->getStyle()
									  ->getNumberFormat()
									  ->setFormatCode('dd/mm/yyyy hh:mm');
							}
							else
							{
								$sheet->getCellByColumnAndRow($keyColumn, $i)->setValue($value);
							}
						}
					}
					else
					{
						$sheet->getCellByColumnAndRow($keyColumn, $i)->setValue($value);
					}
				}

				$i++;
			}
		}

		$sheet->setAutoFilter($spreadsheet->getActiveSheet()->calculateWorksheetDimension());

		// Save file
		$writer = new Xlsx($spreadsheet);
		$writer->save($file_path);

		// Save log
		$this->log(__FUNCTION__, null, $log_message, count($logItem) ? $logItem : null);

		return response()->json(['file_url' => asset($file_url)]);
	}
}
