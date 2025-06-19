<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\OnboardingResource;
use App\Models\Onboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class OnboardingController
 *
 * @resource OnBoarding
 * @package  App\Http\Controllers\v1\Admin
 */
class OnboardingController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
        'frase',
        'frase2',
		'created_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
        'is_active',
		'frase',
		'frase2',
        'created_at'
	];

	/**
	 * Get onboarding
	 *
	 * @permission onboarding.show
	 *
	 * @return OnboardingResource
	 */
	public function index()
	{
        $onboarding = Onboarding::firstOrFail();

		return new OnboardingResource($onboarding);
	}

	/**
	 * Update onboarding
	 *
	 * @permission onboarding.edit
	 *
	 * @param Request   $request
	 * @param Onboarding $onboarding
	 *
	 * @bodyParam  frase string nullable|string|max:191 string
	 * @bodyParam  frase2 string nullable|string|max:191 string
	 * @bodyParam  file file mimes:jpg,jpeg,png,mp4,gif,avi,mov,webm,mpg,ogg,mkv,flv|max:8mb file
	 *
	 * @return OnboardingResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'frase'        => 'sometimes|nullable|string|max:191',
			'frase2'	   => 'sometimes|nullable|string|max:191',
			'file'         => 'mimes:jpg,jpeg,png,mp4,avi,mov,webm,mpg,ogg,mkv,flv|max:8000',
		]);

        $onboarding = Onboarding::firstOrFail();
		$onboarding_old = clone $onboarding;

		DB::beginTransaction();

		try
		{
			// Update
			$onboarding->update($request->only(['frase', 'frase2']));

			// sobe as midias
			if ($request->hasFile('file'))
            {
				$file = $request->file('file');
				$path = 'media';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);
				$type = "image";
				$extensoes_videos = ['mp4', 'avi', 'mov', 'webm', 'mpg', 'ogg', 'mkv', 'flv'];
				$extensoes_gif = ['gif'];

				if (in_array($file->getClientOriginalExtension(), $extensoes_videos))
				{
					// A extensão é um vídeo
					$type = 'video';
				}

				if (in_array($file->getClientOriginalExtension(), $extensoes_gif))
				{
					// A extensão é um gif
					$type = 'gif';
				}

				// exclui a antiga se existir
				if($onboarding->file)
				{
					unlink($onboarding->file);
				}

				$onboarding->update([
					'type' => $type,
					'file' => $path
				]);
			}
			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $onboarding->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $onboarding_old->name, 'Editou um onboarding', $onboarding, $onboarding_old);
		}

		return new OnboardingResource($onboarding);
	}
}
