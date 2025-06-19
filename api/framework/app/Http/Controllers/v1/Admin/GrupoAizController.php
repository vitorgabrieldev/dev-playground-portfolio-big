<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\GrupoAizResource;
use App\Models\GrupoAiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class GrupoAizController
 *
 * @resource Grupo Aiz
 * @package  App\Http\Controllers\v1\Admin
 */
class GrupoAizController extends Controller
{

	/**
	 * Get grupo aiz
	 *
	 * @permission grupo-aiz.edit
	 *
	 * @return GrupoAizResource
	 */
	public function index()
	{
		$grupo_aiz = GrupoAiz::firstOrFail();

		return new GrupoAizResource($grupo_aiz);
	}

	/**
	 * Update grupo aiz
	 *
	 * @permission grupo-aiz.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  text string nullable|string|max:60000 text HTML allowed
	 * @bodyParam  file file mimes:jpg,jpeg,png,mp4,gif,avi,mov,webm,mpg,ogg,mkv,flv|max:8mb file
	 * @bodyParam  delete_file boolean nullable boolean
	 *
	 * @return GrupoAizResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'text' => 'sometimes|nullable|string|max:60000',
			'file'	=> 'mimes:jpg,jpeg,png,mp4,avi,mov,webm,mpg,ogg,mkv,flv|max:8000',
			'delete_file' => 'sometimes|boolean'
		]);

		$grupo_aiz = GrupoAiz::firstOrFail();

		$grupo_aiz_old = clone $grupo_aiz;

		DB::beginTransaction();

		try
		{
			// Update
			$grupo_aiz->fill($request->only(['name', 'text']));
			$grupo_aiz->save();

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
				if($grupo_aiz->file)
				{
					unlink($grupo_aiz->file);
				}

				$grupo_aiz->update([
					'type' => $type,
					'file' => $path
				]);
			}

			if((bool)$request->input('delete_file'))
			{
				// exclui a antiga se existir
				if($grupo_aiz->file)
				{
					unlink($grupo_aiz->file);
				}

				$grupo_aiz->update([
					'type' => null,
					'file' => null
				]);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $grupo_aiz->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $grupo_aiz_old->name, 'Editou o item grupo aiz', $grupo_aiz, $grupo_aiz_old);
		}

		return new GrupoAizResource($grupo_aiz);
	}
}
