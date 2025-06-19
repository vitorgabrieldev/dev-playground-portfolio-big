import React, { Component } from "react";
import { withRouter } from "react-router-dom";

class UISiteFooter extends Component {
	/****************************
	 * Default
	 ****************************/
	themeDefault = () => {
		return (
			<footer id="site-footer" className="site-footer-default">
				<div className="container">
					<div className="inner">
					</div>
				</div>
			</footer>
		)
	}

	render() {
		const {location} = this.props;

		// Disable
		if( location.pathname.startsWith('/app/') || location.pathname.startsWith('/password/reset/') )
		{
			return null;
		}

		return this.themeDefault();
	}
}

export default withRouter(UISiteFooter);
