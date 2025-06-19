<?php

namespace App\Http\Controllers\v1\Admin;

use Illuminate\Http\Request;
use Throwable;

/**
 * Class RichTextEditorController
 *
 * @resource Rich Text Editor
 * @package  App\Http\Controllers\v1\Admin
 */
class RichTextEditorController extends Controller
{

	/**
	 * Upload image
	 *
	 * @param Request $request
	 *
	 * @bodyParam upload file required|mimes:jpeg,png|max:2mb file
	 *
	 * @response {
	 * "url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function uploadImage(Request $request)
	{
		try
		{
			$this->validate($request, [
				'upload' => 'required|mimes:jpeg,png,gif|max:4000',
			]);

			// Get current user
			$user = $this->user();

			$file_url = 'storage/' . $request->file('upload')->storeAs('rich-text-editor', $user->uuid . '_' . uuid() . '.' . $request->file('upload')->extension());
		}
		catch( Throwable $e )
		{
			$error = $e->validator ? $e->validator->errors()->first() : $e->getMessage();

			return response()->json([
				'error' => [
					'message' => $error,
				],
			]);
		}

		return response()->json(['url' => config('app.url') . '/' . $file_url]);
	}
}
