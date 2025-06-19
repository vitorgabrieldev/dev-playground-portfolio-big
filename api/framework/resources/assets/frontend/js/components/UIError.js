import React from "react";
import * as PropTypes from "prop-types";
import { Button } from "antd";

function UIError(props) {
	const {onPressOk, title, text, showIcon, titleClass, subtitleClass, className, buttonProps} = props;

	return (
		<div className={`component-error mt-40 mb-40 ${className}`}>
			{showIcon && <div className="flex-inline flex-middle flex-center mb-20 border-rounded" style={{width: 40, height: 40, lineHeight: 1, background: "#fd8282"}}>
				<i className="icon-x-c" style={{fontSize: 14, color: "#fff"}} />
			</div>}
			{!!title && <h3 className={`text-weight-900 ${titleClass}`}>{title}</h3>}
			<p className={subtitleClass}>{text}</p>
			<Button type="primary" {...buttonProps} onClick={onPressOk}>Tentar novamente</Button>
		</div>
	);
}

UIError.propTypes = {
	onPressOk    : PropTypes.func,
	title        : PropTypes.string,
	text         : PropTypes.string,
	showIcon     : PropTypes.bool,
	titleClass   : PropTypes.any,
	subtitleClass: PropTypes.any,
	className    : PropTypes.string,
	buttonProps  : PropTypes.object,
}

UIError.defaultProps = {
	onPressOk    : () => null,
	title        : 'Ocorreu um erro!',
	text         : '',
	showIcon     : true,
	titleClass   : '',
	subtitleClass: '',
	className    : 'text-center',
	buttonProps  : {},
}

export default UIError;