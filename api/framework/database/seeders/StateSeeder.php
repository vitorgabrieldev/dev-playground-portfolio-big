<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{

	/**
	 * Auto generated seed file
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('states')->insert([
			0  => [
				'id'   => 1,
				'uuid' => uuid(),
				'name' => 'Acre',
				'abbr' => 'AC',
			],
			1  => [
				'id'   => 2,
				'uuid' => uuid(),
				'name' => 'Alagoas',
				'abbr' => 'AL',
			],
			2  => [
				'id'   => 3,
				'uuid' => uuid(),
				'name' => 'Amazonas',
				'abbr' => 'AM',
			],
			3  => [
				'id'   => 4,
				'uuid' => uuid(),
				'name' => 'Amapá',
				'abbr' => 'AP',
			],
			4  => [
				'id'   => 5,
				'uuid' => uuid(),
				'name' => 'Bahia',
				'abbr' => 'BA',
			],
			5  => [
				'id'   => 6,
				'uuid' => uuid(),
				'name' => 'Ceará',
				'abbr' => 'CE',
			],
			6  => [
				'id'   => 7,
				'uuid' => uuid(),
				'name' => 'Distrito Federal',
				'abbr' => 'DF',
			],
			7  => [
				'id'   => 8,
				'uuid' => uuid(),
				'name' => 'Espírito Santo',
				'abbr' => 'ES',
			],
			8  => [
				'id'   => 9,
				'uuid' => uuid(),
				'name' => 'Goiás',
				'abbr' => 'GO',
			],
			9  => [
				'id'   => 10,
				'uuid' => uuid(),
				'name' => 'Maranhão',
				'abbr' => 'MA',
			],
			10 => [
				'id'   => 11,
				'uuid' => uuid(),
				'name' => 'Minas Gerais',
				'abbr' => 'MG',
			],
			11 => [
				'id'   => 12,
				'uuid' => uuid(),
				'name' => 'Mato Grosso do Sul',
				'abbr' => 'MS',
			],
			12 => [
				'id'   => 13,
				'uuid' => uuid(),
				'name' => 'Mato Grosso',
				'abbr' => 'MT',
			],
			13 => [
				'id'   => 14,
				'uuid' => uuid(),
				'name' => 'Pará',
				'abbr' => 'PA',
			],
			14 => [
				'id'   => 15,
				'uuid' => uuid(),
				'name' => 'Paraiba',
				'abbr' => 'PB',
			],
			15 => [
				'id'   => 16,
				'uuid' => uuid(),
				'name' => 'Pernambuco',
				'abbr' => 'PE',
			],
			16 => [
				'id'   => 17,
				'uuid' => uuid(),
				'name' => 'Piauí',
				'abbr' => 'PI',
			],
			17 => [
				'id'   => 18,
				'uuid' => uuid(),
				'name' => 'Paraná',
				'abbr' => 'PR',
			],
			18 => [
				'id'   => 19,
				'uuid' => uuid(),
				'name' => 'Rio de Janeiro',
				'abbr' => 'RJ',
			],
			19 => [
				'id'   => 20,
				'uuid' => uuid(),
				'name' => 'Rio Grande do Norte',
				'abbr' => 'RN',
			],
			20 => [
				'id'   => 21,
				'uuid' => uuid(),
				'name' => 'Rondônia',
				'abbr' => 'RO',
			],
			21 => [
				'id'   => 22,
				'uuid' => uuid(),
				'name' => 'Roraima',
				'abbr' => 'RR',
			],
			22 => [
				'id'   => 23,
				'uuid' => uuid(),
				'name' => 'Rio Grande do Sul',
				'abbr' => 'RS',
			],
			23 => [
				'id'   => 24,
				'uuid' => uuid(),
				'name' => 'Santa Catarina',
				'abbr' => 'SC',
			],
			24 => [
				'id'   => 25,
				'uuid' => uuid(),
				'name' => 'Sergipe',
				'abbr' => 'SE',
			],
			25 => [
				'id'   => 26,
				'uuid' => uuid(),
				'name' => 'São Paulo',
				'abbr' => 'SP',
			],
			26 => [
				'id'   => 27,
				'uuid' => uuid(),
				'name' => 'Tocantins',
				'abbr' => 'TO',
			],
		]);
	}
}
