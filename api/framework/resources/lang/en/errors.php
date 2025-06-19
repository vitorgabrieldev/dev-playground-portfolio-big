<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Exceptions
	|--------------------------------------------------------------------------
	*/

	'unknown_exception'               => 'Error type \':type\'',
	'not_found_exception'             => 'Sorry, the page you are looking for could not be found.',
	'model_not_found_exception'       => 'Record not found.',
	'model_not_allowed'               => 'Method Not Allowed.',
	'validation_fields'               => 'The given data was invalid.',
	'access_denied'                   => 'Sorry, you are not authorized to access this page.',
	'authorization_denied'            => 'Sorry, you are not authorized to access this page.',
	'authentication_exception'        => 'Unauthorized - Check the "Authorization" in the header request.',
	'relation_not_found_exception'    => 'Call to undefined relationship [:relation].',
	'throttle_requests_exception'     => 'Too Many Attempts.',
	'invalid_passport_client'         => 'Check the configuration of the parameters \':clientId\' and \':clientSecret\'.',
	'user_type_exception'             => 'You do not have the valid type for this request.',
	'user_permission_exception'       => 'You do not have permission for this action.',
	'post_too_large_exception'        => 'Request payload is larger than the server is willing or able to process.',
	'general_exception'               => 'An error occurred, please try again later.',
	'oauth_login_invalid_client'      => 'Client authentication failed.',
	'oauth_login_invalid_credentials' => 'The user credentials were incorrect.',
	'oauth_refresh_invalid_request'   => 'The refresh token is invalid.',

	/*
	|--------------------------------------------------------------------------
	| General errors
	|--------------------------------------------------------------------------
	*/
	'general'                         => [
		'push_cant_delete_past_date' => 'You cannot delete an already sent push.',
		'required_search_field'      => 'Must enter at least one search field.',
		'zipcode_not_found'          => 'Zip code not found.',
	],

	/*
	|--------------------------------------------------------------------------
	| Repository exceptions
	|--------------------------------------------------------------------------
	*/
	'repository'                      => [
		'invalid_orderby'              => 'Invalid column in \'OrderBy\'. Only available :fields.',
		'invalid_limit'                => 'Invalid limit. The maximum allowed is \':limit_max\'.',
		'edit_system'                  => 'Item \':id\' is reserved for the system and can not be edited.',
		'delete_system'                => 'Item \':id\' is reserved for the system and can not be deleted.',
		'delete_current_user'          => 'You can not delete your own user.',
		'update_current_user'          => 'You can not edit your own user.',
		'delete_role_with_users'       => 'You can not delete a role that has users.',
		'invalid_permissions_selected' => 'The selected permissions are invalid.',
		'invalid_roles_selected'       => 'The selected roles are invalid.',
		'user_is_not_the_owner'        => 'You do not own this record.',
	],

];
