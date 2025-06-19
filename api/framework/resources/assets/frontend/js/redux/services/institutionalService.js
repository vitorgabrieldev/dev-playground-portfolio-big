import { api } from "./../../config/api";

const basePath = "institutional";

/**
 * Show about
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const about = (cancelToken) => {
	return api.get(`${basePath}/about-app`, {cancelToken});
};

/**
 * Show cookie policy
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const cookiePolicy = (cancelToken) => {
	return api.get(`${basePath}/cookie-policy`, {cancelToken});
};

/**
 * Show privacy policy
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const privacyPolicy = (cancelToken) => {
	return api.get(`${basePath}/privacy-policy`, {cancelToken});
};

/**
 * Show terms of use
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const termsOfUse = (cancelToken) => {
	return api.get(`${basePath}/terms-of-use`, {cancelToken});
};
