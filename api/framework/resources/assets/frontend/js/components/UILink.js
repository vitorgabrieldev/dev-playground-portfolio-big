import React from "react";
import * as PropTypes from "prop-types";
import { Link } from "react-router-dom";
import zenscroll from './../vendor/zenscroll/zenscroll';

function UILink(props) {
	const {scroll, scrollTo, scrollTime, insideSlide, children, ...rest} = props;

	let mouseMoved = false;

	function onClick(e) {
		if( insideSlide && mouseMoved )
		{
			// Stop click action
			e.stopPropagation();
			e.preventDefault();
			return false;
		}

		if( scroll )
		{
			const el = document.querySelector(scrollTo);

			if( !el ) console.log.warn(`Element ${scrollTo} not found.`);

			// Scroll to element
			zenscroll.to(el, scrollTime);
		}
	}

	const linkProps = {
		...rest,
	};

	if( insideSlide )
	{
		linkProps.onDragStart = (e) => {
			e.preventDefault();
		}
		linkProps.onMouseMove = () => {
			mouseMoved = true;
		};
		linkProps.onMouseDown = () => {
			mouseMoved = false;
		};
	}

	return (
		<Link {...linkProps} onClick={onClick}>{children}</Link>
	);
}

UILink.propTypes = {
	to         : PropTypes.any,
	scroll     : PropTypes.bool,
	scrollTo   : PropTypes.string,
	scrollTime : PropTypes.number,
	insideSlide: PropTypes.bool,
	className  : PropTypes.string,
}

UILink.defaultProps = {
	scroll     : true,
	scrollTo   : "#site-main",
	scrollTime : 0,
	insideSlide: false,
}

export default UILink;