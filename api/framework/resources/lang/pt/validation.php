<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/

	'accepted'             => 'Deve ser aceito.',
	'active_url'           => 'Não é uma URL válida.',
	'after'                => 'Deve conter uma data posterior a :date.',
	'after_or_equal'       => 'Deve conter uma data superior ou igual a :date.',
	'alpha'                => 'Deve conter apenas letras.',
	'alpha_dash'           => 'Deve conter apenas letras, números e traços.',
	'alpha_num'            => 'Deve conter apenas letras e números .',
	'array'                => 'Deve conter um array.',
	'before'               => 'Deve conter uma data anterior a :date.',
	'before_or_equal'      => 'Deve conter uma data inferior ou igual a :date.',
	'between'              => [
		'numeric' => 'Deve conter um número entre :min e :max.',
		'file'    => 'Deve conter um arquivo de :min a :max kilobytes.',
		'string'  => 'Deve conter entre :min a :max caracteres.',
		'array'   => 'Deve conter de :min a :max itens.',
	],
	'boolean'              => 'Deve conter o valor verdadeiro ou falso.',
	'confirmed'            => 'A confirmação não coincide.',
	'date'                 => 'Deve ser uma data válida.',
	'date_equals'          => 'Deve ser uma data igual :date.',
	'date_format'          => 'A data informada não corresponde ao formato :format.',
	'different'            => 'Deve conter um valor diferente do campo :other.',
	'digits'               => 'Deve conter :digits dígitos.',
	'digits_between'       => 'Deve conter entre :min a :max dígitos.',
	'dimensions'           => 'Deve ser uma dimensão de imagem válida.',
	'distinct'             => 'Contém um valor duplicado.',
	'email'                => 'Deve ser um endereço de email válido.',
	'ends_with'            => 'Deve terminar com um dos seguintes: :values',
	'exists'               => 'O valor selecionado é inválido.',
	'file'                 => 'Deve conter um arquivo.',
	'filled'               => 'Deve conter um valor.',
	'gt'                   => [
		'numeric' => 'Deve ser maior que :value.',
		'file'    => 'Deve ser maior que :value kilobytes.',
		'string'  => 'Deve ser maior que :value caracteres.',
		'array'   => 'Deve ter mais que :value itens.',
	],
	'gte'                  => [
		'numeric' => 'Deve ser maior ou igual a :value.',
		'file'    => 'Deve ser maior ou igual a :value kilobytes.',
		'string'  => 'Deve ser maior ou igual a :value caracteres.',
		'array'   => 'Deve ter :value itens ou mais.',
	],
	'image'                => 'Deve conter uma imagem.',
	'in'                   => 'Não contém um valor válido.',
	'in_array'             => 'Não existe em :other.',
	'integer'              => 'Deve conter um número inteiro.',
	'ip'                   => 'Deve conter um IP válido.',
	'ipv4'                 => 'Deve conter um IPv4 válido.',
	'ipv6'                 => 'Deve conter um IPv6 válido.',
	'json'                 => 'Deve conter uma string JSON válida.',
	'lt'                   => [
		'numeric' => 'Deve ser menor que :value.',
		'file'    => 'O arquivo deve ser menor que :value kilobytes.',
		'string'  => 'Deve ser menor que :value caracteres.',
		'array'   => 'Deve ter menos que :value itens.',
	],
	'lte'                  => [
		'numeric' => 'Deve ser menor ou igual a :value.',
		'file'    => 'O arquivo deve ser menor ou igual a :value kilobytes.',
		'string'  => 'Deve ser menor ou igual a :value caracteres.',
		'array'   => 'Não deve ter mais que :value itens.',
	],
	'max'                  => [
		'numeric' => 'Não pode conter um valor superior a :max.',
		'file'    => 'Não pode conter um arquivo com mais de :max kilobytes.',
		'string'  => 'Não pode conter mais de :max caracteres.',
		'array'   => 'Deve conter no máximo :max itens.',
	],
	'mimes'                => 'Deve conter um arquivo do tipo: :values.',
	'mimetypes'            => 'Deve conter um arquivo do tipo: :values.',
	'min'                  => [
		'numeric' => 'Deve conter um número superior ou igual a :min.',
		'file'    => 'Deve conter um arquivo com no mínimo :min kilobytes.',
		'string'  => 'Deve conter no mínimo :min caracteres.',
		'array'   => 'Deve conter no mínimo :min itens.',
	],
	'multiple_of'          => 'Deve ser um múltiplo de :value',
	'not_in'               => 'Contém um valor inválido.',
	'not_regex'            => 'O formato do valor informado é inválido.',
	'numeric'              => 'Deve conter um valor numérico.',
	'password'             => 'A senha está incorreta.',
	'present'              => 'Deve estar presente.',
	'regex'                => 'O formato do valor informado é inválido.',
	'required'             => 'Campo obrigatório.',
	'required_if'          => 'Obrigatório quando o valor do campo :other é igual a :value.',
	'required_unless'      => 'Obrigatório a menos que :other esteja presente em :values.',
	'required_with'        => 'Obrigatório quando :values está presente.',
	'required_with_all'    => 'Obrigatório quando um dos :values está presente.',
	'required_without'     => 'Obrigatório quando :values não está presente.',
	'required_without_all' => 'Obrigatório quando nenhum dos :values está presente.',
	'same'                 => 'Deve conter o mesmo valor de :other.',
	'size'                 => [
		'numeric' => 'Deve conter o número :size.',
		'file'    => 'Deve conter um arquivo com o tamanho de :size kilobytes.',
		'string'  => 'Deve conter :size caracteres.',
		'array'   => 'Deve conter :size itens.',
	],
	'starts_with'          => 'Deve começar com um dos seguintes: :values',
	'string'               => 'Deve ser uma string.',
	'timezone'             => 'Deve conter um fuso horário válido.',
	'unique'               => 'O valor informado já está em uso.',
	'uploaded'             => 'Falha no upload do arquivo.',
	'url'                  => 'O formato da URL é inválido.',
	'uuid'                 => 'Deve ser um UUID válido.',

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'cep'       => 'Deve ser no formato 99999-999.',
	'phone'     => 'Deve ser no formato (99) 9999-9999 ou (99) 99999-9999.',
	'cellphone' => 'Deve ser no formato (99) 99999-9999.',
	'latitude'  => 'Deve conter uma latitude válida.',
	'longitude' => 'Deve conter uma longitude válida.',
	'min_words' => 'Deve conter no mínimo :value palavras.',
	'max_words' => 'Não deve ter mais que :value palavras.',
	'cpf'       => 'Deve ser um CPF válido.',
	'cnpj'      => 'Deve ser um CNPJ válido.',
	'cpf_cnpj'  => 'Deve ser um CPF/CNPJ válido.',
	'youtube'   => 'Deve ser uma url de vídeo do YouTube válida.',

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
		'name'           => [
			'min_words' => 'Informe seu nome completo.',
		],
		'email'          => [
			'unique' => 'O e-mail informado já está em uso.',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap our attribute placeholder
	| with something more reader friendly such as "E-Mail Address" instead
	| of "email". This simply helps us make our message more expressive.
	|
	*/

	'attributes'               => [
		'password'                  => 'Senha',
		'password_new'              => 'Nova senha',
		'password_new_confirmation' => 'Confirmar nova senha',
		'localization'              => 'Localização',
		'lat'                       => 'Latitude',
		'lon'                       => 'Longitude',
		'date_start'                => 'Data inicial',
		'date_end'                  => 'Data final',
	],

	/*
	|--------------------------------------------------------------------------
	| Validation complement
	|--------------------------------------------------------------------------
	*/
	'invalid_current_password' => 'Senha atual inválida.',
];
