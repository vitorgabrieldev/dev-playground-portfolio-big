<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationProvider extends ServiceProvider
{

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		/**
		 * Validate CEP
		 *
		 * Accepted format
		 * 99999-999
		 */
		Validator::extend('cep', function ($attribute, $value, $parameters, $validator) {
			return preg_match('/^[0-9]{5}[-][0-9]{3}$/', $value);
		});

		/**
		 * Validate phone
		 *
		 * Accepted format
		 * (99) 9999-9999
		 * (99) 99999-9999
		 */
		Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
			return preg_match('/^\([0-9]{2}\) [0-9]{4,5}[-][0-9]{4}$/', $value);
		});

		/**
		 * Validate cellphone
		 *
		 * Accepted format
		 * (99) 99999-9999
		 */
		Validator::extend('cellphone', function ($attribute, $value, $parameters, $validator) {
			return preg_match('/^\([0-9]{2}\) [0-9]{5}[-][0-9]{4}$/', $value);
		});

		/**
		 * Validate latitude
		 */
		Validator::extend('latitude', function ($attribute, $value, $parameters, $validator) {
			return preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,11})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,11})?))$/', $value);
		});

		/**
		 * Validate longitude
		 */
		Validator::extend('longitude', function ($attribute, $value, $parameters, $validator) {
			return preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,11})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,11})?))$/', $value);
		});

		/**
		 * Validate min words
		 */
		Validator::extend('min_words', function ($attribute, $value, $parameters, $validator) {
			return str_word_count($value) >= $parameters[0];
		});

		Validator::replacer('min_words', function ($message, $attribute, $rule, $parameters) {
			return str_replace(':value', $parameters[0], $message);
		});

		/**
		 * Validate max words
		 */
		Validator::extend('max_words', function ($attribute, $value, $parameters, $validator) {
			return str_word_count($value) <= $parameters[0];
		});

		Validator::replacer('max_words', function ($message, $attribute, $rule, $parameters) {
			return str_replace(':value', $parameters[0], $message);
		});

		/**
		 * Validate HEX
		 */
		Validator::extend('hex', function ($attribute, $value, $parameters, $validator) {
			$pattern = '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/';

			if( in_array('onlyFull', $parameters) )
			{
				$pattern = '/^#([a-fA-F0-9]{6})$/';
			}

			return preg_match($pattern, $value);
		});

		/**
		 * Validate CPF/CNPJ
		 *
		 * Accepted format
		 * 000.000.000-00 or 00.000.000/0000-00 default
		 * 00000000000 or 00000000000000 with "onlyNumbers" param
		 */
		Validator::extend('cpf_cnpj', function ($attribute, $value, $parameters, $validator) {
			$finalValue  = preg_replace('/\D/', '', $value);
			$onlyNumbers = false;

			if( in_array('onlyNumbers', $parameters) )
			{
				$onlyNumbers = true;
			}

			// CPF 11, CNPJ 14
			if( $onlyNumbers and strlen($value) !== 11 and strlen($value) !== 14 )
			{
				return false;
			}
			// CPF 14, CNPJ 18
			elseif( !$onlyNumbers and strlen($value) !== 14 and strlen($value) !== 18 )
			{
				return false;
			}

			return $this->cpf($attribute, $finalValue, $parameters, $validator) or $this->cnpj($attribute, $finalValue, $parameters, $validator);
		});

		/**
		 * Validate CPF
		 *
		 * Accepted format
		 * 000.000.000-00 default
		 * 00000000000 with "onlyNumbers" param
		 */
		Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
			$finalValue  = preg_replace('/\D/', '', $value);
			$onlyNumbers = false;

			if( in_array('onlyNumbers', $parameters) )
			{
				$onlyNumbers = true;
			}

			if( $onlyNumbers and strlen($value) !== 11 )
			{
				return false;
			}
			elseif( !$onlyNumbers and strlen($value) !== 14 )
			{
				return false;
			}

			return $this->cpf($attribute, $finalValue, $parameters, $validator);
		});

		/**
		 * Validate CNPJ
		 *
		 * Accepted format
		 * 00.000.000/0000-00 default
		 * 00000000000000 with "onlyNumbers" param
		 */
		Validator::extend('cnpj', function ($attribute, $value, $parameters, $validator) {
			$finalValue  = preg_replace('/\D/', '', $value);
			$onlyNumbers = false;

			if( in_array('onlyNumbers', $parameters) )
			{
				$onlyNumbers = true;
			}

			if( $onlyNumbers and strlen($value) !== 14 )
			{
				return false;
			}
			elseif( !$onlyNumbers and strlen($value) !== 18 )
			{
				return false;
			}

			return $this->cnpj($attribute, $finalValue, $parameters, $validator);
		});

		/**
		 * Validate YouTube url
		 */
		Validator::extend('youtube', function ($attribute, $value, $parameters, $validator) {
			preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $value, $matches);

			return isset($matches[1]);
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * CPF
	 *
	 * @param $attribute
	 * @param $value
	 * @param $parameters
	 * @param $validator
	 *
	 * @return bool
	 */
	public function cpf($attribute, $value, $parameters, $validator)
	{
		$cpf = $value;
		$num = [];

		// Cria um array com os valores
		for( $i = 0; $i < (strlen($cpf)); $i++ )
		{
			$num[] = $cpf[$i];
		}

		if( count($num) != 11 )
		{
			return false;
		}
		else
		{
			// Combinações como 00000000000 e 22222222222 embora
			// não sejam cpfs reais resultariam em cpfs
			// válidos após o calculo dos dígitos verificares e
			// por isso precisam ser filtradas nesta parte.
			for( $i = 0; $i < 10; $i++ )
			{
				if( $num[0] == $i && $num[1] == $i && $num[2] == $i && $num[3] == $i && $num[4] == $i && $num[5] == $i && $num[6] == $i && $num[7] == $i && $num[8] == $i )
				{
					return false;
					break;
				}
			}
		}

		// Calcula e compara o primeiro dígito verificador
		$j          = 10;
		$multiplica = [];

		for( $i = 0; $i < 9; $i++ )
		{
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}

		$soma  = array_sum($multiplica);
		$resto = $soma % 11;

		if( $resto < 2 )
		{
			$dg = 0;
		}
		else
		{
			$dg = 11 - $resto;
		}

		if( $dg != $num[9] )
		{
			return false;
		}

		// Calcula e compara o segundo dígito verificador
		$j = 11;

		for( $i = 0; $i < 10; $i++ )
		{
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}

		$soma  = array_sum($multiplica);
		$resto = $soma % 11;

		if( $resto < 2 )
		{
			$dg = 0;
		}
		else
		{
			$dg = 11 - $resto;
		}

		if( $dg != $num[10] )
		{
			return false;
		}

		return true;
	}

	/**
	 * CNPJ
	 *
	 * @param $attribute
	 * @param $value
	 * @param $parameters
	 * @param $validator
	 *
	 * @return bool
	 */
	public function cnpj($attribute, $value, $parameters, $validator)
	{
		$cnpj = $value;
		$num  = [];

		// Cria um array com os valores
		for( $i = 0; $i < (strlen($cnpj)); $i++ )
		{
			$num[] = $cnpj[$i];
		}

		// Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
		if( count($num) != 14 )
		{
			return false;
		}

		// Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria
		// um cnpj válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
		if( $num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0 )
		{
			return false;
		}
		// Etapa 4: Calcula e compara o primeiro dígito verificador.
		else
		{
			$j          = 5;
			$multiplica = [];

			for( $i = 0; $i < 4; $i++ )
			{
				$multiplica[$i] = $num[$i] * $j;
				$j--;
			}

			$j = 9;

			for( $i = 4; $i < 12; $i++ )
			{
				$multiplica[$i] = $num[$i] * $j;
				$j--;
			}

			$soma  = array_sum($multiplica);
			$resto = $soma % 11;

			if( $resto < 2 )
			{
				$dg = 0;
			}
			else
			{
				$dg = 11 - $resto;
			}

			if( $dg != $num[12] )
			{
				return false;
			}
		}

		// Etapa 5: Calcula e compara o segundo dígito verificador.
		$j = 6;
		for( $i = 0; $i < 5; $i++ )
		{
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}

		$j = 9;

		for( $i = 5; $i < 13; $i++ )
		{
			$multiplica[$i] = $num[$i] * $j;
			$j--;
		}

		$soma  = array_sum($multiplica);
		$resto = $soma % 11;

		if( $resto < 2 )
		{
			$dg = 0;
		}
		else
		{
			$dg = 11 - $resto;
		}

		if( $dg != $num[13] )
		{
			return false;
		}

		return true;
	}
}
