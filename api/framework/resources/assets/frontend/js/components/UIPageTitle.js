import React from "react";
import * as PropTypes from "prop-types";

function UIPageTitle(props) {
	const {title, subtitle, titleClass, subtitleClass, className, ...rest} = props;

	return (
		<header {...rest} className={`component-page-title ${className}`}>
			<h1 className={titleClass}>{title}</h1>
			{!!subtitle && <p className={subtitleClass}>{subtitle}</p>}
		</header>
	);
}

UIPageTitle.propTypes = {
	title        : PropTypes.string,
	subtitle     : PropTypes.string,
	titleClass   : PropTypes.any,
	subtitleClass: PropTypes.any,
	className    : PropTypes.string,
}

UIPageTitle.defaultProps = {
	title        : '',
	subtitle     : '',
	titleClass   : '',
	subtitleClass: '',
	className    : '',
}

export default UIPageTitle;