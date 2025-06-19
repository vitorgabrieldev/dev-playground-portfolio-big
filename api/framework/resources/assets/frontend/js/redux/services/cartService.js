import { api } from "./../../config/api";

const basePath = "cart";

/**
 * Get cart
 *
 * @param {Object} options
 *
 * @returns {*}
 */
export const getCart = (options) => {
	const options_default = {};

	// Merge config
	options = Object.assign({}, options_default, options);

	let params    = [];
	let params_qs = "";

	if( options.hasOwnProperty("cart_hash") )
	{
		params.push(`cart_hash=${options.cart_hash}`);
	}

	if( params.length )
	{
		params_qs = `?${params.join("&")}`;
	}

	return api.get(`${basePath}${params_qs}`);
};

/**
 * Add
 *
 * @param options
 *
 * @returns {*}
 */
export const add = (options) => {
	return api.post(`${basePath}/add`, options);
};

/**
 * Edit
 *
 * @param options
 *
 * @returns {*}
 */
export const edit = (options) => {
	return api.post(`${basePath}/edit`, options);
};

/**
 * Remove
 *
 * @param options
 *
 * @returns {*}
 */
export const remove = (options) => {
	return api.post(`${basePath}/remove`, options);
};

/**
 * Add coupon
 *
 * @param options
 *
 * @returns {*}
 */
export const couponAdd = (options) => {
	return api.post(`${basePath}/coupon`, options);
};

/**
 * Remove coupon
 *
 * @param options
 *
 * @returns {*}
 */
export const couponRemove = (options) => {
	return api.post(`${basePath}/coupon-remove`, options);
};

/**
 * Calculate shipping
 *
 * @param options
 *
 * @returns {*}
 */
export const shippingCalculate = (options) => {
	return api.post(`${basePath}/shipping-calculate`, options);
};

/**
 * Add shipping
 *
 * @param options
 *
 * @returns {*}
 */
export const shippingAdd = (options) => {
	return api.post(`${basePath}/shipping`, options);
};

/**
 * Shipping address
 *
 * @param options
 *
 * @returns {*}
 */
export const shippingAddress = (options) => {
	return api.post(`${basePath}/shipping-address`, options);
};

/**
 * Billing address
 *
 * @param options
 *
 * @returns {*}
 */
export const billingAddress = (options) => {
	return api.post(`${basePath}/billing-address`, options);
};

/**
 * Finish
 *
 * @param options
 *
 * @returns {*}
 */
export const finish = (options) => {
	return api.post(`${basePath}/finish`, options);
};

