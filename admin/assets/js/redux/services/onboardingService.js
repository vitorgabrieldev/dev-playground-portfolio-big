import { api } from "./../../config/api";
import { appendToFormData } from "./../../helpers/form";

const basePath = "onboarding";

/**
 * Get onboarding
 *
 * @param cancelToken
 *
 * @returns {Promise<T>}
 */
export const getOnboarding = (cancelToken) => {
	return api.get(`${basePath}`, {cancelToken});
};

/**
 * Edit
 *
 * @param {Object} options
 *
 * @returns {Promise<T>}
 */
export const edit = (options) => {
	const formData = new FormData();

	for( let key in options )
	{
		if( options.hasOwnProperty(key) )
		{
			appendToFormData(formData, key, options[key]);
		}
	}

	return api.post(`${basePath}`, formData);
};