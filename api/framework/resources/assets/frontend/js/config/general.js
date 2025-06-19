import { osName, browserName } from "./../helpers/client";

// -----------------------------------------------------------------------------
// General
// -----------------------------------------------------------------------------
export const IS_DEBUG    = process.env.NODE_ENV === 'development';
export const ENV = process.env.NODE_ENV;

export const CLIENT_DATA = {
	os_name     : osName(),
	browser_name: browserName(),
};
export const ROUTE_PATH  = IS_DEBUG ? '/' : '/';

// -----------------------------------------------------------------------------
// API
// -----------------------------------------------------------------------------
let url = "localhost:3032";
let endpoint_customer = `http://${url}/api/v1/customer/`;
let endpoint_admin = `http://${url}/api/v1/admin/`;
let soket_auth = `http://${url}/broadcasting/auth`;
let site_url = "https://localhost";
let admin_url = "http://localhost:3033/";

export const API_URL = endpoint_customer;
export const SOCKET_URL = `${url}`;
export const SOCKET_AUTH = soket_auth;

export const API_ADMIN_URL = endpoint_admin;
export const SITE_URL      = site_url;
export const ADMIN_URL     = admin_url;

// -----------------------------------------------------------------------------
// Media Query
// -----------------------------------------------------------------------------
export const MQ_MOBILE       = "(max-width: 767px)";
export const MQ_DESKTOP_DOWN = "(max-width: 1199px)";
export const MQ_DESKTOP      = "(min-width: 1200px)";

export const IS_MQ_MOBILE       = window.matchMedia(MQ_MOBILE).matches;
export const IS_MQ_DESKTOP_DOWN = window.matchMedia(MQ_DESKTOP_DOWN).matches;
export const IS_MQ_DESKTOP      = window.matchMedia(MQ_DESKTOP).matches;

export const IS_TOUCH_DEVICE = (('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0));

// -----------------------------------------------------------------------------
// Errors
// -----------------------------------------------------------------------------
export const API_ERRO_TYPE_VALIDATION   = "validation";
export const API_ERRO_TYPE_API          = "api";
export const API_ERRO_TYPE_SERVER       = "server";
export const API_ERRO_TYPE_CONNECTION   = "connection";
export const API_ERRO_TYPE_OTHER        = "other";
export const API_ERRO_TYPE_ACCESS_TOKEN = "access_token";
export const API_ERRO_TYPE_CANCEL       = "cancel";

// -----------------------------------------------------------------------------
// SEO
// -----------------------------------------------------------------------------
export const SEO_TITLE     = "Grupo AIZ";
export const SEO_SEPARATOR = " - ";
