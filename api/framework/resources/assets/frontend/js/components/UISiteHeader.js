import React, { Component, Fragment } from "react";
import { withRouter } from "react-router-dom";
import { Col, Row } from "antd";

class UISiteHeader extends Component {
	themeDefault = () => {
		return (
			<header id="site-header" className="site-header-simple">
				<div className="container">
					<Row gutter={10} align="middle" justify="center" className="inner">
						<Col xs={9} md={6} lg={4}>
							<h1 className="logo">
								<a href={window.config?.url_root}>
									<img src="images/frontend/logos/logo.svg" alt={window.config?.app_name} />
								</a>
							</h1>
						</Col>
					</Row>
				</div>
			</header>
		)
	}

	render() {
		const {location} = this.props;

		// Disable
		if( location.pathname.startsWith('/app/') )
		{
			return null;
		}

		return this.themeDefault();
	}
}

export default withRouter(UISiteHeader);
