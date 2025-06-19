import { REHYDRATE } from "redux-persist";
import { generalConstants } from "./../constants";

import moment from "moment";

const reducerKey = "general";

const defaultState = {
	newsLikes   : [],
	newsDislikes: [],
	cookiePolicy: null,
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
					newsLikes   : persistCache.newsLikes || defaultState.newsLikes,
					cookiePolicy: persistCache.cookiePolicy || defaultState.cookiePolicy,
				};
			}

			return Object.assign({}, state, persistUpdate);

		case generalConstants.COOKIE_POLICY:
			return Object.assign({}, state, {
				cookiePolicy: moment().format('YYYY-MM-DD HH:mm:ss'),
			});

		case generalConstants.NEWS_LIKE:
			let newNewsLikes    = new Set([...state.newsLikes]);
			let newNewsDislikes = new Set([...state.newsDislikes]);

			if( action.data.like )
			{
				newNewsLikes.add(action.data.id);
				newNewsDislikes.delete(action.data.id);
			}
			else
			{
				newNewsDislikes.add(action.data.id);
				newNewsLikes.delete(action.data.id);
			}

			return Object.assign({}, state, {
				newsLikes   : [...newNewsLikes],
				newsDislikes: [...newNewsDislikes],
			});

		default:
			return state;
	}
}
