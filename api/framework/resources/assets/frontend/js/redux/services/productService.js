import { api } from "./../../config/api";

const basePath = "products";

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

	if( options.hasOwnProperty("type") )
	{
		params.push(`type=${options.type}`);
	}

	if( options.hasOwnProperty("category") )
	{
		params.push(`category=${options.category}`);
	}

	if( options.hasOwnProperty("subcategories") )
	{
		options.subcategories.forEach((item, index) => {
			params.push(`subcategories[${index}]=${item}`);
		});
	}

	if( options.hasOwnProperty("brands") )
	{
		options.brands.forEach((item, index) => {
			params.push(`brands[${index}]=${item}`);
		});
	}

	if( options.hasOwnProperty("lines") )
	{
		options.lines.forEach((item, index) => {
			params.push(`lines[${index}]=${item}`);
		});
	}

	if( options.hasOwnProperty("postages") )
	{
		options.postages.forEach((item, index) => {
			params.push(`postages[${index}]=${item}`);
		});
	}

	if( options.hasOwnProperty("is_offer") )
	{
		params.push(`is_offer=${options.is_offer}`);
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
 * Calculate shipping
 *
 * @param options
 *
 * @returns {*}
 */
export const shippingCalculate = (options) => {
	return api.post(`${basePath}/shipping-calculate/${options.uuid}`, options);
};

/**
 * Autocomplete
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getAutocomplete = (options, cancelToken) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("search") )
	{
		params.push(`search=${options.search}`);
	}

	if( options.hasOwnProperty("orderBy") )
	{
		params.push(`orderBy=${options.orderBy}`);
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}/autocomplete${params_qs}`, {cancelToken});
};

/**
 * Get all reviews
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getReviews = (options, cancelToken) => {
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

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}/reviews/${options.uuid}${params_qs}`, {cancelToken});
};

/**
 * Create reviews
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const createReview = (options, cancelToken) => {
	return api.post(`${basePath}/reviews/${options.uuid}`, options, {cancelToken});
};

