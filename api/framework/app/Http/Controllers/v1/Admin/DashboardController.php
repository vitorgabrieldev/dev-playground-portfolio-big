<?php

namespace App\Http\Controllers\v1\Admin;

use Illuminate\Http\Request;
use App\Services\EcwidService;

/**
 * Class DashboardController
 *
 * @resource Dashboard
 * @package  App\Http\Controllers\v1\Company
 */
class DashboardController extends Controller
{

	/**
	 * Get summary of data
	 *
	 * @param Request $request
	 * @param EcwidService $ecwid
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function index(Request $request, EcwidService $ecwid)
    {
        try {
            $products = $ecwid->getProducts();

            $data = [
                'ecwid_products_total'   => $products['total'] ?? 0,
            ];

            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar os produtos',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
