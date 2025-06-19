import { api } from "./../../config/api";

const basePath = "webservice";

/**
 * Cities
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getCities = (options, cancelToken) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("search") )
	{
		params.push(`search=${options.search}`);
	}

	if( options.hasOwnProperty("state_abbr") )
	{
		params.push(`state_abbr=${options.state_abbr}`);
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}/cities${params_qs}`, {cancelToken});
};

/**
 * States
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getStates = (options, cancelToken) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("search") )
	{
		params.push(`search=${options.search}`);
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}/states${params_qs}`, {cancelToken});
};

/**
 * Find cep
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const findZipcode = (options) => {
	return api.get(`${basePath}/zipcode/${options.zipcode}`);
};
