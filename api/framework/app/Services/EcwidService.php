<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class EcwidService
{
    protected $baseUrl;
    protected $storeId;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.ecwid.url');
        $this->storeId = config('services.ecwid.store_id');
        $this->token = config('services.ecwid.token');
    }

    /**
     * Busca marcas da loja Ecwid
     *
     * @param int $limit Número de resultados (padrão: 100)
     * @param int $offset Offset para paginação
     * @return array
     * @throws \Exception
     */
    public function getBrands($limit = 100, $offset = 0)
    {
        return Cache::remember("ecwid_brands_{$offset}", now()->addMinutes(10), function () use ($limit, $offset) {
            $url = "{$this->baseUrl}{$this->storeId}/brands";

            $response = Http::withToken($this->token)
                ->get($url, [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            $errorMessage = 'Erro ao consultar marcas na Ecwid: ' . $response->body();
            \Log::error($errorMessage, [
                'url' => $url,
                'limit' => $limit,
                'offset' => $offset,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            throw new \Exception($errorMessage);
        });
    }

    /**
     * Busca produtos da loja Ecwid
     *
     * @param int $limit Número de resultados (padrão: 100)
     * @param int $offset Offset para paginação
     * @param array $queryParams Parâmetros adicionais de filtro (opcional)
     * @return array
     * @throws \Exception
     */
    public function getProducts($limit = 100, $offset = 0, array $queryParams = [])
    {
        return Cache::remember("ecwid_products_{$offset}_" . md5(json_encode($queryParams)), now()->addMinutes(10), function () use ($limit, $offset, $queryParams) {
            $url = "{$this->baseUrl}{$this->storeId}/products";

            $params = array_merge([
                'limit' => $limit,
                'offset' => $offset,
            ], $queryParams);

            $response = Http::withToken($this->token)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            $errorMessage = 'Erro ao consultar produtos na Ecwid: ' . $response->body();
            \Log::error($errorMessage, [
                'url' => $url,
                'params' => $params,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            throw new \Exception($errorMessage);
        });
    }
}