import { api } from "./../../config/api";

const basePath = "auth";

/**
 * Login user
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const login = (options, cancelToken) => {
	return api.post(`${basePath}/login`, options, {cancelToken});
};

/**
 * Facebook register/login
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const facebook = (options, cancelToken) => {
	return api.post(`${basePath}/facebook`, options, {cancelToken});
};

/**
 * Register user
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const register = (options, cancelToken) => {
	return api.post(`${basePath}/register`, options, {cancelToken});
};

/**
 * Complete register user
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const registerComplete = (options, cancelToken) => {
	return api.post(`${basePath}/register-complete`, options, {cancelToken});
};

/**
 * Logout logged user
 *
 * @returns {Promise<T>}
 */
export const logout = () => {
	return api.delete(`${basePath}/logout`);
};

/**
 * Password recovery
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const passwordRecovery = (options, cancelToken) => {
	return api.post(`${basePath}/password/recovery`, options, {cancelToken});
};

/**
 * Get logged user
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getUserData = (cancelToken = null) => {
	const data = {};

	if( cancelToken )
	{
		data.cancelToken = cancelToken;
	}

	return api.get(`${basePath}/user`, data);
};

/**
 * Edit user
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const edit = (options, cancelToken) => {
	return api.post(`${basePath}/edit`, options, {cancelToken});
};

/**
 * Change user password
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const changePassword = (options, cancelToken) => {
	return api.post(`${basePath}/change-password`, options, {cancelToken});
};

/**
 * Change user avatar
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const changeAvatar = (options, cancelToken) => {
	const form = new FormData();
	form.append("avatar", options.avatar, options.avatar.name);

	return api.post(`${basePath}/change-avatar`, form, {cancelToken});
};

/**
 * Remove account
 *
 * @returns {Promise<T>}
 */
export const removeAccount = () => {
	return api.delete(`${basePath}/remove-account`);
};
