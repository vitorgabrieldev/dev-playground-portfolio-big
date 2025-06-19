<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Exceptions
	|--------------------------------------------------------------------------
	*/

	'unknown_exception'               => 'Erro do tipo \':type\'',
	'not_found_exception'             => 'Desculpe, a página que você está procurando não foi encontrada.',
	'model_not_found_exception'       => 'Registro não encontrado.',
	'model_not_allowed'               => 'Método não permitido.',
	'validation_fields'               => 'Os dados fornecidos não são válidos.',
	'access_denied'                   => 'Desculpe, você não está autorizado a acessar esta página.',
	'authorization_denied'            => 'Desculpe, você não está autorizado a acessar esta página.',
	'authentication_exception'        => 'Não autorizado - Verifique a "Authorization" no cabeçalho da requisição.',
	'relation_not_found_exception'    => 'Não existe o relacionamento [:relation].',
	'throttle_requests_exception'     => 'Muitas tentativas.',
	'invalid_passport_client'         => 'Verifique a configuração dos parâmetros \':clientId\' e \':clientSecret\'.',
	'user_type_exception'             => 'Você não possui o tipo válido para está requisição.',
	'user_permission_exception'       => 'Você não possui permissão para esta ação.',
	'post_too_large_exception'        => 'A carga útil da solicitação é maior do que o servidor deseja ou pode processar.',
	'general_exception'               => 'Ocorreu um erro, por favor tente novamente mais tarde.',
	'oauth_login_invalid_client'      => 'Autenticação do cliente falhou.',
	'oauth_login_invalid_credentials' => 'As credenciais do usuário estavam incorretas.',
	'oauth_refresh_invalid_request'   => 'O token de atualização é inválido.',

	/*
	|--------------------------------------------------------------------------
	| General errors
	|--------------------------------------------------------------------------
	*/
	'general'                         => [
		'push_cant_delete_past_date' => 'Não pode deletar um push já enviado.',
		'required_search_field'      => 'Deve informar pelo menos um campo de busca.',
		'zipcode_not_found'          => 'CEP não encontrado.',
	],

	/*
	|--------------------------------------------------------------------------
	| Repository exceptions
	|--------------------------------------------------------------------------
	*/
	'repository'                      => [
		'invalid_orderby'              => 'Coluna inválida em \'orderBy\'. Somente disponível :fields.',
		'invalid_limit'                => 'Limite inválido. O máximo permitido é de \':limit_max\'.',
		'edit_system'                  => 'O item \':id\' é reservado para o sistema e não pode ser editado.',
		'delete_system'                => 'O item \':id\' é reservado para o sistema e não pode ser deletado.',
		'delete_current_user'          => 'Você não pode deletar seu próprio usuário.',
		'update_current_user'          => 'Você não pode editar seu próprio usuário.',
		'delete_role_with_users'       => 'Você não pode deletar um papél que possui usuários.',
		'invalid_permissions_selected' => 'As permissões selecionadas são inválidas.',
		'invalid_roles_selected'       => 'Os papéis selecionados são inválidos.',
		'user_is_not_the_owner'        => 'Você não é proprietário deste registro.',
	],

];
