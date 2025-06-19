import { cartConstants } from "./../constants";
import { cartService } from "./../services";

/**
 * Re-load cart data from server
 *
 * @param options
 *
 * @returns {function(*)}
 */
export const refreshCart = (options) => {
	return (dispatch, getState) => {
		dispatch({
			type: cartConstants.CART_REQUEST,
		});

		const options = {
			cart_hash: getState().cart.cartHash,
		};

		cartService.getCart(options).then((response) => {
			dispatch({
				type: cartConstants.CART_SUCCESS,
				data: response.data.data,
			});
		})
		.catch((data) => {
			dispatch({
				type: cartConstants.CART_ERROR,
				data: {
					error_type   : data?.error_type ?? '',
					error_message: String(data),
					error_errors : data?.error_errors ?? '',
				}
			});
		});
	};
};

/**
 * Save cart
 *
 * @param data
 *
 * @returns {function(*)}
 */
export const saveCart = (data) => {
	return {
		type: cartConstants.CART_SAVE,
		data: data,
	};
};

/**
 * Reset cart
 *
 * @returns {{data: *, type: string}}
 */
export const resetCart = () => {
	return {
		type: cartConstants.CART_RESET,
	}
};
