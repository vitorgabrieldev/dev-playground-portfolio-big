<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use GuzzleHttp\Client;

if( !function_exists('is_environment_prod') )
{
	/**
	 * In production environment
	 *
	 * @return bool
	 */
	function is_environment_prod()
	{
		return app()->environment() === 'prod' || app()->environment() === 'production';
	}
}

if( !function_exists('is_environment_local') )
{
	/**
	 * In local environment
	 *
	 * @return bool
	 */
	function is_environment_local()
	{
		return app()->environment() === 'local';
	}
}

if( !function_exists('is_environment_production') )
{
	/**
	 * In production environment
	 *
	 * @return bool
	 */
	function is_environment_production()
	{
		return app()->environment() === 'production';
	}
}

if( !function_exists('user_has_permission') )
{
	/**
	 * Check if current user has permission
	 *
	 * @param array $permissions
	 *
	 * @return bool
	 */
	function user_has_permission(array $permissions)
	{
		// Only logged
		if( !Auth::check() )
		{
			return false;
		}

		$cache_id = 'user_permissions';

		if( app()->has($cache_id) )
		{
			// Get cachead user permissions
			$userPermissions = app($cache_id);
		}
		else
		{
			app()->singleton($cache_id, function ($app) {
				// Get current user
				$user = Auth::user();

				// Load relations
				$user->load('roles.permissions');

				return $user->myPermissions();
			});

			// Get cachead
			$userPermissions = app($cache_id);
		}

		// Has permission?
		foreach( $permissions as $permission )
		{
			if( in_array($permission, $userPermissions) )
			{
				return true;
			}
		}

		return false;
	}
}

if( !function_exists('get_client_os') )
{
	/**
	 * Get client OS
	 *
	 * @return string
	 */
	function get_client_os()
	{
		$os        = 'desktop';
		$userAgent = Request::server('HTTP_USER_AGENT');

		if( stristr($userAgent, 'ipad') or stristr($userAgent, 'iphone') )
		{
			$os = 'ios';
		}
		elseif( stristr($userAgent, 'android') )
		{
			$os = "android";
		}

		return $os;
	}
}

if( !function_exists('remove_break_line') )
{
	/**
	 * Remove break lines
	 *
	 * @param $val
	 *
	 * @return string|string[]|null
	 */
	function remove_break_line($val)
	{
		return preg_replace("/[\n\r]/", "", $val);
	}
}

if( !function_exists('division') )
{
	/**
	 * Division
	 *
	 * @param      $a
	 * @param      $b
	 * @param bool $round
	 *
	 * @return float|int
	 */
	function division($a, $b, $round = false)
	{
		if( !$a ) return 0;
		if( !$b ) return 0;

		return $round ? round($a / $b, 2) : $a / $b;
	}
}

if( !function_exists('faker_image') )
{
	/**
	 * Download a remote random image to disk and return its location
	 *
	 * Requires curl, or allow_url_fopen to be on in php.ini.
	 *
	 * @param null         $dir
	 * @param int          $width
	 * @param int          $height
	 * @param bool         $fullPath
	 * @param bool         $randomize
	 * @param bool         $gray
	 * @param bool|integer $blur
	 *
	 * @return bool|RuntimeException|string
	 * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
	 *
	 */
	function faker_image($dir = null, $width = 640, $height = 480, $fullPath = false, $randomize = true, $gray = false, $blur = false)
	{
		$dir = is_null($dir) ? sys_get_temp_dir() : $dir; // GNU/Linux / OS X / Windows compatible
		// Validate directory path
		if( !is_dir($dir) || !is_writable($dir) )
		{
			throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
		}

		// Generate a random filename. Use the server address so that a file
		// generated at the same time on a different server won't have a collision.
		$name     = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
		$filename = $name . '.jpg';
		$filepath = $dir . DIRECTORY_SEPARATOR . $filename;

		$url = "{$width}/{$height}/";

		$query_string = [];

		if( $gray )
		{
			$query_string[] = "grayscale";
		}

		if( $blur )
		{
			$query_string[] = 'blur' . (is_int($blur) ? '=' . $blur : '');
		}

		if( $randomize )
		{
			$query_string[] = mt_rand(pow(10, 5 - 1), pow(10, 5) - 1);
		}

		if( count($query_string) )
		{
			$url .= "?" . implode('&', $query_string);
		}

		$url = "https://picsum.photos/" . $url;

		// save file
		if( function_exists('curl_exec') )
		{
			// use cURL
			$fp = fopen($filepath, 'w');
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			$success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
			fclose($fp);
			curl_close($ch);

			if( !$success )
			{
				unlink($filepath);

				// could not contact the distant URL or HTTP error - fail silently.
				return false;
			}
		}
		elseif( ini_get('allow_url_fopen') )
		{
			// use remote fopen() via copy()
			$success = copy($url, $filepath);
		}
		else
		{
			return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
		}

		return $fullPath ? $filepath : $filename;
	}
}

if( !function_exists('faker') )
{
	/**
	 * New faker instance
	 *
	 * @param $language
	 *
	 * @return \Faker\Generator
	 */
	function faker($language = null)
	{
		return Factory::create($language ?? config('app.faker_locale'));
	}
}

if( !function_exists('uuid') )
{
	/**
	 * Generate Uuid 4
	 *
	 * @return string
	 */
	function uuid()
	{
		return Str::uuid()->toString();
	}
}

if( !function_exists('url_image_cache') )
{
	/**
	 * Get image urls cache
	 *
	 * @param $image
	 *
	 * @return array
	 */
	function url_image_cache($image)
	{
		return [
			// Admin listing
			'admin_listing'        => route('imagecache', ['admin_listing', $image]),
			'admin_listing_medium' => route('imagecache', ['admin_listing_medium', $image]),
			// General
			'preloader'            => route('imagecache', ['preloader', $image]),
			// Front listing
			'app_listing'          => route('imagecache', ['app_listing', $image]),
			'app_detail'           => route('imagecache', ['app_detail', $image]),
		];
	}
}

if( !function_exists('current_user_admin') )
{
	/**
	 * Get logged user
	 *
	 * @return mixed|App\Models\User;
	 */
	function current_user_admin()
	{
		return request()->user('users') ?? request()->user('web_users');
	}
}

if( !function_exists('current_user_customer') )
{
	/**
	 * Get logged customer
	 *
	 * @return mixed|App\Models\Customer;
	 */
	function current_user_customer()
	{
		return
		request()->user('web_customers') ??
		request()->user('socialnetwork_customers') ??
		request()->user('customers');
	}
}

if( !function_exists('icon_by_file_name') )
{
	/**
	 * Get icon class by filename extension
	 *
	 * @param $name
	 *
	 * @return string
	 */
	function icon_by_file_name($name)
	{
		$icons = [
			'csv'  => 'fa-file-csv',
			'doc'  => 'fa-file-word',
			'docx' => 'fa-file-word',
			'pdf'  => 'fa-file-pdf',
			'ppt'  => 'fa-file-powerpoint',
			'rar'  => 'fa-file-archive',
			'rtf'  => 'fa-file-alt',
			'txt'  => 'fa-file-alt',
			'zip'  => 'fa-file-archive',
		];

		$exp       = explode('.', $name);
		$extension = end($exp);

		return $icons[$extension] ?? 'fa-file';
	}
}

if( !function_exists('only_numbers') )
{
	/**
	 * Get only numbers
	 *
	 * @param $val
	 *
	 * @return array|string|string[]|null
	 */
	function only_numbers($val)
	{
		return preg_replace('/[^0-9]/', '', $val);
	}
}

if( !function_exists('remove_accents') )
{
	/**
	 * Remove accents from a string
	 *
	 * @param $val
	 *
	 * @return array|string|string[]|null
	 */
	function remove_accents($string)
	{
		$unwanted_array = [
			'á'=>'a', 'à'=>'a', 'ã'=>'a', 'â'=>'a', 'ä'=>'a',
			'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
			'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
			'ó'=>'o', 'ò'=>'o', 'õ'=>'o', 'ô'=>'o', 'ö'=>'o',
			'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u',
			'ç'=>'c', 'ñ'=>'n',
			'Á'=>'A', 'À'=>'A', 'Ã'=>'A', 'Â'=>'A', 'Ä'=>'A',
			'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E',
			'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I',
			'Ó'=>'O', 'Ò'=>'O', 'Õ'=>'O', 'Ô'=>'O', 'Ö'=>'O',
			'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U',
			'Ç'=>'C', 'Ñ'=>'N'
		];

		return strtr($string, $unwanted_array);
	}
}

if( !function_exists('youtube_id') )
{
	/**
	 * Get youtube video id
	 *
	 * @param $url
	 *
	 * @return string
	 */
	function youtube_id($url)
	{
		preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);

		return $matches[1] ?? null;
	}
}

if( !function_exists('youtube_url_embed') )
{
	/**
	 * Get youtube url embed
	 *
	 * @param      $url
	 * @param bool $autoplay
	 *
	 * @return string
	 */
	function youtube_url_embed($url, $autoplay = true)
	{
		return 'https://www.youtube-nocookie.com/embed/' . youtube_id($url) . '?autoplay=' . ($autoplay ? 1 : 0) . '&showinfo=0&rel=0&modestbranding=1&playsinline=1';
	}
}

if( !function_exists('youtube_image') )
{
	/**
	 * Get image
	 *
	 * @param $url
	 * @param $fallback_image
	 *
	 * @return string
	 */
	function youtube_image($url, $fallback_image = null)
	{
		$id = youtube_id($url);

		if( $id )
		{
			return 'https://img.youtube.com/vi/' . $id . '/0.jpg';
		}

		return $fallback_image ?? 'images/frontend/no-image.jpg';
	}
}

if( !function_exists('card_brand') )
{
	/**
	 * Get card brand by number
	 *
	 * @param $card
	 *
	 * @return string|null
	 */
	function card_brand($card)
	{
		$brands = [
			'visa'       => '/^4[0-9]\d+$/',
			'mastercard' => '/^((5(([1-2]|[4-5])[0-9]{8}|0((1|6)([0-9]{7}))|3(0(4((0|[2-9])[0-9]{5})|([0-3]|[5-9])[0-9]{6})|[1-9][0-9]{7})))|((508116)\\d{4,10})|((502121)\\d{4,10})|((589916)\\d{4,10})|(2[0-9]{15})|(67[0-9]{14})|(506387)\\d{4,10})/',
			'diners'     => '/^(?:5[45]|36|30[0-5]|3095|3[8-9])\d+$/',
			'discover'   => '/^6(?:011|22(12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|9[01][0-9]|92[0-5])|5|4|2[4-6][0-9]{3}|28[2-8][0-9]{2})\d+$/',
			'elo'        => '/^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^504175|^627780|^63(6297|6368|6369)|(65003[5-9]|65004[0-9]|65005[01])|(65040[5-9]|6504[1-3][0-9])|(65048[5-9]|65049[0-9]|6505[0-2][0-9]|65053[0-8])|(65054[1-9]|6505[5-8][0-9]|65059[0-8])|(65070[0-9]|65071[0-8])|(65072[0-7])|(65090[1-9]|6509[1-6][0-9]|65097[0-8])|(65165[2-9]|6516[67][0-9])|(65500[0-9]|65501[0-9])|(65502[1-9]|6550[34][0-9]|65505[0-8])|^(506699|5067[0-6][0-9]|50677[0-8])|^(509[0-8][0-9]{2}|5099[0-8][0-9]|50999[0-9])|^65003[1-3]|^(65003[5-9]|65004\d|65005[0-1])|^(65040[5-9]|6504[1-3]\d)|^(65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])|^(65054[1-9]|6505[5-8]\d|65059[0-8])|^(65070\d|65071[0-8])|^65072[0-7]|^(65090[1-9]|65091\d|650920)|^(65165[2-9]|6516[6-7]\d)|^(65500\d|65501\d)|^(65502[1-9]|6550[3-4]\d|65505[0-8])$/',
			'amex'       => '/^3[47][0-9]{13}$/',
			'jcb'        => '/^(?:35[2-8][0-9])\d+$/',
			'aura'       => '/^((?!504175))^((?!5067))(^50[0-9])/',
			'hipercard'  => '/^606282|^3841(?:[0|4|6]{1})0/',
			'maestro'    => '/^(?:50|5[6-9]|6[0-9])\d+$/',
			'unionpay'   => '/^(62[0-9]{14,17})$/'
		];

		foreach( $brands as $brand => $regex )
		{
			if( preg_match($regex, only_numbers($card)) )
			{
				return $brand;
			}
		}

		return null;
	}
}

if( !function_exists('slug') )
{
	/**
	 * Generate a URL friendly "slug"
	 *
	 * @param string $title
	 * @param string $separator
	 * @param string $language
	 * @param bool   $allow_slash
	 *
	 * @return string
	 */
	function slug(string $title, string $separator = '-', string $language = 'en', bool $allow_slash = false): string
	{
		$title = $language ? Str::ascii($title, $language) : $title;

		// Convert all dashes/underscores into separator
		$flip = $separator === '-' ? '_' : '-';

		$title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

		// Replace @ with the word 'at'
		$title = str_replace('@', $separator . 'at' . $separator, $title);

		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		if( $allow_slash )
		{
			$title = preg_replace('![^' . preg_quote($separator) . '\pL\pN/\s]+!u', '', Str::lower($title));
		}
		else
		{
			$title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', Str::lower($title));
		}

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

		return trim($title, $separator);
	}
}

if( !function_exists('site_default_config') )
{
	/**
	 * Load default config to site
	 *
	 * @return object
	 */
	function site_default_config()
	{
		$return = [];

		$return['url_root'] = url('/');
		$return['app_name'] = config('app.name');

		return (object) $return;
	}
}

if( !function_exists('is_url') )
{
	/**
	 * Check value is url valid
	 *
	 * @param string $value
	 *
	 * @return object
	 */
	function is_url($value)
	{
		$is_url = false;

		if (filter_var($value, FILTER_VALIDATE_URL))
		{
			$is_url = true;
		}

		return $is_url;
	}
}

if( !function_exists('kmToRange') )
{
	/**
	 * Converte KM para range em graus
	 *
	 * @param int $distanceKm
	 * @return float
	 */
	function kmToRange(int $distanceKm)
	{
		// 1 grau ≈ 111 km
		$kmPerDegree = 111;
		return $distanceKm / $kmPerDegree;
	}
}

if( !function_exists('haversineDistance') )
{
	/**
	 * Função para calcular a distância usando a fórmula de Haversine
	 *
	 * @param float $lat1
	 * @param float $lon1
	 * @param float $lat2
	 * @param float $lon2
	 * @return float
	 */
	function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2)
	{
		$earthRadius = 6371; // Raio da Terra em quilômetros

		$latFrom = deg2rad($lat1);
		$lonFrom = deg2rad($lon1);
		$latTo = deg2rad($lat2);
		$lonTo = deg2rad($lon2);

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
		cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

		return $angle * $earthRadius;
	}
}

if( !function_exists('getDrivingDistance') )
{
	/**
	 * Função para calcular a distância por estrada usando a API do Google Maps7
	 *
	 * @param float|string $originLat
	 * @param float|string $originLon
	 * @param float|string $destLat
	 * @param float|string $destLon
	 * @return array
	 */
	function getDrivingDistance($originLat, $originLon, $destLat, $destLon)
	{
		$client = new Client();
		$apiKey = config('api.googlemaps_key');

		// Monta a URL da API do Google Maps Directions
		$url = "https://maps.googleapis.com/maps/api/directions/json?" .
			"origin={$originLat},{$originLon}&" .
			"destination={$destLat},{$destLon}&" .
			"key={$apiKey}";

		// Faz a requisição à API
		$response = $client->get($url);
		$data = json_decode($response->getBody(), true);

		// Verifica se a requisição foi bem-sucedida
		if ($data['status'] === 'OK')
		{
			// Retorna a distância em metros
			return [
				'text_distance' => $data['routes'][0]['legs'][0]['distance']['text'] ?? '',
				'distance' => $data['routes'][0]['legs'][0]['distance']['value'] / 1000, // Converte para km
				'time' => $data['routes'][0]['legs'][0]['duration']['value'] > 0 ? secondsToHoursMinutes($data['routes'][0]['legs'][0]['duration']['value']) : "1 minuto",
			];
		} else {
			throw new Exception("Erro ao calcular distância: " . $data['status']);
		}
	}
}

if( !function_exists('secondsToHoursMinutes') )
{
	/**
	 * converter segundos para horas com texto
	 *
	 * @param int $seconds
	 * @return string
	 */
	function secondsToHoursMinutes($seconds)
	{
		$hours = floor($seconds / 3600); // Converte segundos em horas
		$minutes = floor(($seconds % 3600) / 60); // Converte o restante em minutos

		// Monta a string em português
		$result = '';

		if ($hours > 0)
		{
			$result .= $hours . ' hora' . ($hours > 1 ? 's' : '');
		}

		if ($minutes > 0)
		{
			if ($hours > 0)
			{
				$result .= ' e ';
			}
			$result .= $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
		}
		return $result;
	}
}

if( !function_exists('order_status_color') )
{
	/**
	 * Get survey status color
	 *
	 * @param $status
	 *
	 * @return string
	 */
	function order_status_color($status)
	{
		switch( $status )
		{
			case 'paid':
				$return = '#2d98c2';
				break;

			case 'declined':
				$return = '#000';
				break;

			case 'canceled':
				$return = '#b20000';
				break;

			case 'accepted':
				$return = '#ffa500';
				break;

			case 'delivered':
				$return = '#039c33';
				break;

			default:
				$return = '#8a8a8a';
				break;
		}

		return $return;
	}
}

if( !function_exists('separatePhone') )
{
	/**
	 * Metodo para separar o numero de telefone em um array (areaCode[0] e number[1])
	 *
	 * @param string $phone
	 * @return Array
	 */
	function separatePhone($phone) : Array
	{
		// Extrai o DDD (remove parênteses e espaços)
		preg_match('/\((\d{2})\)/', $phone, $matches);
		$areaCode = $matches[1] ?? null;

		// Remove tudo que não é dígito e o DDD já extraído
		$number = preg_replace('/[^0-9]/', '', $phone);
		$number = substr($number, 2); // Remove os primeiros 2 dígitos (DDD)

		return [
			'areaCode' => $areaCode,
			'number' => $number,
		];
	}
}

if( !function_exists('validarCPF') )
{
	/**
	 * Metodo para validar se o CPF é valido ou não
	 *
	 * @param string $cpf
	 * @return bool
	 */
	function validarCPF(string $cpf): bool
	{
		// Remove caracteres não numéricos
		$cpf = preg_replace('/[^0-9]/', '', $cpf);

		// Verifica se tem 11 dígitos ou se todos são iguais
		if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}

		// Validação do primeiro dígito verificador
		for ($i = 0, $soma = 0; $i < 9; $i++) {
			$soma += (int) $cpf[$i] * (10 - $i);
		}

		$resto = $soma % 11;
		$digito1 = ($resto < 2) ? 0 : 11 - $resto;

		if ($cpf[9] != $digito1) {
			return false;
		}

		// Validação do segundo dígito verificador
		for ($i = 0, $soma = 0; $i < 10; $i++) {
			$soma += (int) $cpf[$i] * (11 - $i);
		}

		$resto = $soma % 11;
		$digito2 = ($resto < 2) ? 0 : 11 - $resto;

		return $cpf[10] == $digito2;
	}
}

if( !function_exists('validarCNPJ') )
{
	/**
	 * Metodo para validar se o CNPJ é valido ou não
	 *
	 * @param string $cnpj
	 * @return bool
	 */
    function validarCNPJ(string $cnpj): bool
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se tem 14 dígitos ou se todos são iguais
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;

        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $pesos[$i];
        }

        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        if ($cnpj[12] != $digito1) {
            return false;
        }

        // Validação do segundo dígito verificador
        $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;

        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $pesos[$i];
        }

        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return $cnpj[13] == $digito2;
    }
}

if( !function_exists('identificarDocumento') )
{
	/**
	 * Metodo para verificar se o documento é CPF ou CNPJ
	 *
	 * @param string $cnpj
	 * @return bool
	 */
    function identificarDocumento(string $documento): string
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);
        $tamanho = strlen($documento);

        if ($tamanho === 11)
        {
            return 'CPF';
        }
        elseif ($tamanho === 14)
        {
            return 'CNPJ';
        }

        return 'DESCONHECIDO';
    }
}
