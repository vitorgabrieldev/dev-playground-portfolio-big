<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IndexController extends Controller
{

	/**
	 * Default front
	 * 
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function index(Request $request)
	{
		$data = [
			'seo_title'       => [],
			'seo_url'         => $request->url(),
			'seo_type'        => 'website',
			'seo_description' => config('app.name'),
			'seo_image'       => asset('images/frontend/logos/opengraph.jpg'),
		];

		// Prepare title
		$data['seo_title'] = implode(' - ', array_reverse($data['seo_title']));

		if( Str::length($data['seo_title']) > 60 )
		{
			$data['seo_title'] = Str::limit($data['seo_title'], 60);
		}

		$data['seo_title'] = ($data['seo_title'] ? $data['seo_title'] . ' - ' : '') . config('app.name');

		return view('frontend.pages.index', $data);
	}
}