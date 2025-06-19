import { api } from "./../../config/api";

const basePath = "customers-deleted";

/**
 * Get all
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const getAll = (options) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("page") )
	{
		params.push(`page=${options.page}`);
	}

	if( options.hasOwnProperty("limit") )
	{
		params.push(`limit=${options.limit}`);
	}

	if( options.hasOwnProperty("search") )
	{
		params.push(`search=${options.search}`);
	}

	if( options.hasOwnProperty("orderBy") )
	{
		params.push(`orderBy=${options.orderBy}`);
	}

	if( options.hasOwnProperty("is_deleted_confirmed") )
	{
		params.push(`is_deleted_confirmed=${options.is_deleted_confirmed}`);
	}

	if( options.hasOwnProperty("created_at") )
	{
		options.created_at.forEach((item, index) => {
			params.push(`created_at[${index}]=${item}`);
		});
	}

	if( options.hasOwnProperty("deleted_at") )
	{
		options.deleted_at.forEach((item, index) => {
			params.push(`deleted_at[${index}]=${item}`);
		});
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	if( options.exportItems )
	{
		return api.get(`${basePath}/export${params_qs}`);
	}
	else
	{
		return api.get(`${basePath}${params_qs}`);
	}
};

/**
 * Show
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const show = (options) => {
	return api.get(`${basePath}/${options.uuid}`);
};