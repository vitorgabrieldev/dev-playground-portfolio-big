import { applyMiddleware, combineReducers, createStore } from "redux";
import thunk from "redux-thunk";
import logger from "redux-logger";
import { persistStore, persistReducer } from "redux-persist";
import storage from "redux-persist/lib/storage";

import {
	authRedurcer,
	cartRedurcer,
	generalRedurcer,
} from "./../reducers";

const IS_DEBUG = process.env.NODE_ENV === 'development';

const persistConfig = {
	key      : "root",
	storage  : storage,
	whitelist: [
		"auth",
		"cart",
		"general",
	],
};

const rootReducer = combineReducers({
	auth   : authRedurcer,
	cart   : cartRedurcer,
	general: generalRedurcer,
});

const persistedReducer = persistReducer(persistConfig, rootReducer);

const middleware = [thunk];

if( IS_DEBUG )
{
	middleware.push(logger);
}

export const store     = createStore(persistedReducer, applyMiddleware(...middleware));
export const persistor = persistStore(store);
