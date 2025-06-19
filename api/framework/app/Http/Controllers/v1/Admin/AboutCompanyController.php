<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\AboutCompanyResource;
use App\Models\AboutCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Owenoj\LaravelGetId3\GetId3;
use Throwable;

/**
 * Class AboutCompanyController
 *
 * @resource About company
 * @package  App\Http\Controllers\v1\Admin
 */
class AboutCompanyController extends Controller
{

	/**
	 * Get about company
	 *
	 * @permission about-company.edit
	 *
	 * @return AboutCompanyResource
	 */
	public function index()
	{
		$about = AboutCompany::firstOrFail();

		return new AboutCompanyResource($about);
	}

	/**
	 * Update about company
	 *
	 * @permission about-company.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  file file nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  file_mobile file nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  delete_file boolean boolean boolean Default true
     * @bodyParam  delete_file_mobile boolean boolean boolean Default true
	 * @bodyParam  text string nullable|string|max:60000 text HTML allowed
	 *
	 * @return AboutCompanyResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'name'					=> 'sometimes|required|string|max:191',
			'file'					=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'file_mobile'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
            'delete_file'			=> 'sometimes|required|boolean',
            'delete_file_mobile'	=> 'sometimes|required|boolean',
			'text'					=> 'sometimes|nullable|string|max:60000',
		]);

		$about = AboutCompany::firstOrFail();
        $delete_file = (bool)$request->input('delete_file');
        $delete_file_mobile = (bool)$request->input('delete_file_mobile');

		$about_old = clone $about;

		DB::beginTransaction();

		try
		{
			// Update
			$about->fill($request->only(['name', 'text']));

            if($delete_file)
            {
                // Delete file
                if( $about->file )
                {
                    $file_path = public_path($about->file);
                    if( file_exists($file_path) )
                    {
                        unlink($about->file);
                    }
                }

                $about->file = null;
            }

            if($delete_file_mobile)
            {
                // Delete file
                if( $about->file_mobile )
                {
                    $file_mobile_path = public_path($about->file_mobile);
                    if( file_exists($file_mobile_path) )
                    {
                        unlink($about->file_mobile);
                    }
                }

                $about->file_mobile = null;
            }

			// File
			if( $request->hasFile('file') )
			{
				$file_name   = Str::random(30) . '.jpg';
				$file_folder = 'storage/about/';
				$file_path   = public_path($file_folder) . $file_name;
				$file_url    = $file_folder . $file_name;

				// Save image
				Image::make($request->file('file'))->resize(200, 200, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				})->save($file_path);

				$about->file = $file_url;
			}

			// File mobile
			if( $request->hasFile('file_mobile') )
			{
				$file_name   = Str::random(30) . '.jpg';
				$file_folder = 'storage/about/';
				$file_path   = public_path($file_folder) . $file_name;
				$file_url    = $file_folder . $file_name;

				// Save image
				Image::make($request->file('file_mobile'))->resize(200, 200, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				})->save($file_path);

				$about->file_mobile = $file_url;
			}

			$about->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $about->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $about_old->name, 'Editou sobre a empresa', $about, $about_old);
		}

		return new AboutCompanyResource($about);
	}
}
