import { api } from "./../../config/api";

const basePath = "news";

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

	if( options.hasOwnProperty("category_id") )
	{
		params.push(`category_id=${options.category_id}`);
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
	return api.get(`${basePath}/${options.uuid}`, {cancelToken});
};

/**
 * Show by slug
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const showBySlug = (options, cancelToken) => {
	return api.get(`${basePath}/slug/${options.slug}`, {cancelToken});
};

/**
 * Like/Dislike
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const likeDislike = (options, cancelToken) => {
	return api.post(`${basePath}/like-dislike/${options.id}`, options, {cancelToken});
};
