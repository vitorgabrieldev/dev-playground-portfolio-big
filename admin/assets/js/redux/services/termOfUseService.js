import { api } from "./../../config/api";
import { appendToFormData } from "./../../helpers/form";

const basePath = "terms-of-use";

/**
 * Show
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const show = (cancelToken) => {
	return api.get(basePath, {cancelToken});
};

/**
 * Edit
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const edit = (options) => {
	const { uuid, ...rest } = options;

	const formData = new FormData();
	for (let key in rest) {
		if (rest.hasOwnProperty(key)) {
			appendToFormData(formData, key, rest[key]);
		}
	}
	return api.post(`${basePath}`, formData);
};