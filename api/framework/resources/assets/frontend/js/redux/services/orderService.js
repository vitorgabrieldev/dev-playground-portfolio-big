import { api } from "./../../config/api";

const basePath = "orders";

/**
 * Get all
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getAll = (options, cancelToken) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("orderBy") )
	{
		params.push(`orderBy=${options.orderBy}`);
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}${params_qs}`, {cancelToken});
};

/**
 * Show
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const show = (options, cancelToken) => {
	return api.get(`${basePath}/${options.id}`, {cancelToken});
};

/**
 * Buy again
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const buyAgain = (options) => {
	return api.post(`${basePath}/buy-again/${options.id}`, options);
};
