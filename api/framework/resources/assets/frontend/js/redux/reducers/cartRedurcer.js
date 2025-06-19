import { REHYDRATE } from "redux-persist";
import { cartConstants } from "./../constants";

const reducerKey = "cart";

const defaultState = {
	isLoading   : false,
	cartHasError: false,
	cartError   : "",
	cartHash    : null,
	cart        : null,
};

export default function reducer(state = defaultState, action) {
	switch( action.type )
	{
		case REHYDRATE:
			let persistUpdate = {};

			if( action.payload && action.payload[reducerKey] )
			{
				const persistCache = action.payload[reducerKey];

				persistUpdate = {
					cartHash: persistCache.cartHash || defaultState.cartHash,
				};
			}

			return Object.assign({}, state, persistUpdate);

		case cartConstants.CART_REQUEST:
			return Object.assign({}, state, {
				isLoading   : true,
				cartHasError: false,
				cartError   : "",
			});

		case cartConstants.CART_SUCCESS:
			return Object.assign({}, state, {
				isLoading: false,
				cartHash : action?.data?.uuid ?? null,
				cart     : action?.data ?? null,
			});

		case cartConstants.CART_ERROR:
			return Object.assign({}, state, {
				isLoading   : false,
				cartHasError: true,
				cartError   : action.data.error_message,
			});

		case cartConstants.CART_SAVE:
			return Object.assign({}, state, {
				isLoading   : false,
				cartHasError: false,
				cartError   : "",
				cartHash    : action?.data?.uuid ?? null,
				cart        : action?.data ?? null,
			});

		case cartConstants.CART_RESET:
			return Object.assign({}, state, {
				cartHash: null,
				cart    : null,
			});

		default:
			return state;
	}
}
