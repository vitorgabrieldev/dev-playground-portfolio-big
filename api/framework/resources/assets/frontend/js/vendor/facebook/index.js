/**
 * Init
 *
 * @param appId
 * @param apiVersion
 */
export function init(appId, apiVersion = 'v13.0') {
	if( typeof window.FB !== 'undefined' )
	{
		return false;
	}

	window.fbAsyncInit = () => {
		window.FB.init({
			appId  : appId,
			cookie : true,
			xfbml  : true,
			version: apiVersion,
		});
	};

	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if( d.getElementById(id) )
		{
			return;
		}
		js     = d.createElement(s);
		js.id  = id;
		js.src = "https://connect.facebook.net/pt_BR/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
}