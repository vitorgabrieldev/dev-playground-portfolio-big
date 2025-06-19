import { SEO_TITLE, SEO_SEPARATOR } from "./../config/general";

/**
 * Set title tag
 *
 * @param {string|Array} title
 * @param {boolean} raw
 *
 * @returns {string}
 */
export function setTitle(title, raw = false) {
	let title_;

	if( Array.isArray(title) )
	{
		title_ = title.join(SEO_SEPARATOR);
	}
	else
	{
		title_ = String(title);
	}

	if( raw )
	{
		document.title = title_;
	}
	else if( !title_ )
	{
		document.title = SEO_TITLE;
	}
	else
	{
		if( title_.length > 60 )
		{
			title_ = `${title_.substring(0, 60)}...`;
		}

		document.title = title_ + SEO_SEPARATOR + SEO_TITLE;
	}
}
