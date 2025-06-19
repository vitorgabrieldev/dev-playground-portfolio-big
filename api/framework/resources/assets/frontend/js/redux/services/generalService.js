import { api } from "./../../config/api";
import { API_URL, API_ADMIN_URL } from "./../../config/general";

/**
 * Password reset
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const passwordReset = (options, cancelToken) => {
	let baseURL = API_URL;

	if( options.type === 'users' )
	{
		baseURL = API_ADMIN_URL;
	}

	return api.post("auth/password/reset", options, {
		baseURL    : baseURL,
		cancelToken: cancelToken,
	});
};

/**
 * Verify Account
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const verifyAccount = (options, cancelToken) => {
	let baseURL = API_URL;

	if( options.type === 'users' )
	{
		baseURL = API_ADMIN_URL;
	}

	return api.post("customer/email/verify", options, {
		baseURL    : baseURL,
		cancelToken: cancelToken,
	});
};
