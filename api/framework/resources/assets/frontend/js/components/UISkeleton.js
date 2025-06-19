import React, { Fragment } from "react";
import * as PropTypes from "prop-types";

import { IS_MQ_MOBILE } from "./../config/general";

function UISkeleton(props) {
	const {type, w, h, hxs, className} = props;

	if( type === 'text' )
	{
		return (
			<div className={className}>
				<div className="component-skeleton" style={{width: "38%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "61%", height: 16}} />
			</div>
		);
	}
	else if( type === 'longtext' )
	{
		return (
			<div className={className}>
				<div className="component-skeleton" style={{width: "38%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "61%", height: 16}} />
				<br />
				<div className="component-skeleton" style={{width: "55%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "85%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "61%", height: 16}} />
				<br />
				<div className="component-skeleton" style={{width: "45%", height: 16}} />
				<div className="component-skeleton" style={{width: "60%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "85%", height: 16}} />
				<div className="component-skeleton" style={{width: "100%", height: 16}} />
				<div className="component-skeleton" style={{width: "61%", height: 16}} />
			</div>
		);
	}

	let height = h;

	if( IS_MQ_MOBILE && hxs )
	{
		height = hxs;
	}

	return (
		<div className={`component-skeleton ${className}`} style={{width: w, height: height}} />
	);
}

UISkeleton.propTypes = {
	type     : PropTypes.oneOf(['text', 'longtext', '']).isRequired,
	w        : PropTypes.any.isRequired,
	h        : PropTypes.any.isRequired,
	hxs      : PropTypes.any,
	className: PropTypes.string,
}

UISkeleton.defaultProps = {
	type     : '',
	w        : "100%",
	h        : 16,
	className: '',
}

export default UISkeleton;