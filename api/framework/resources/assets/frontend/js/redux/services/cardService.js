import { api } from "./../../config/api";

const basePath = "cards";

/**
 * Get all
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getAll = (cancelToken) => {
	return api.get(basePath, {cancelToken});
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
 * Create
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const create = (options, cancelToken) => {
	return api.post(basePath, options, {cancelToken});
};

/**
 * Delete
 *
 * @param {Object} options
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const destroy = (options, cancelToken) => {
	return api.delete(`${basePath}/${options.id}`, {cancelToken});
};
