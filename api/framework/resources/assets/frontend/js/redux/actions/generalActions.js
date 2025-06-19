import { generalConstants } from "./../constants";

/**
 * Confirm cookie policy
 *
 * @returns {{type: string}}
 */
export const cookiePolicy = () => {
	return {
		type: generalConstants.COOKIE_POLICY,
	}
};

/**
 * News like/dislike
 *
 * @param id
 * @param like
 *
 * @returns {{data: {like, id}, type: string}}
 */
export const newsLikeDislike = (id, like) => {
	return {
		type: generalConstants.NEWS_LIKE,
		data: {
			id  : id,
			like: like,
		}
	}
};
