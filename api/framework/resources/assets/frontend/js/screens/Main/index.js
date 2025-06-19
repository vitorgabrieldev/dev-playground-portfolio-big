import React, { Component } from "react";
import { connect } from "react-redux";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import { ConfigProvider } from "antd";

import moment from "moment";
import "moment/locale/pt-br";

moment.locale("pt-br");

import pt_BR from 'antd/es/locale/pt_BR';

import { ROUTE_PATH, IS_TOUCH_DEVICE } from "./../../config/general";

import { ROUTES } from "./../../config/routes";

import Error404 from "./../../screens/Error404";

import {
	UISiteFooter,
	UISiteHeader,
} from "./../../components";

class Main extends Component {
	componentDidMount() {
		// Touch class on html tag
		document.getElementsByTagName('html')[0].className += (IS_TOUCH_DEVICE ? 'touch' : 'no-touch');
	}

	render() {
		return (
			<ConfigProvider locale={pt_BR}>
				<BrowserRouter basename={ROUTE_PATH}>
					<UISiteHeader />
					<Switch>
						{ROUTES.map((route, i) => (
							<Route
								key={i}
								exact={route?.exact ?? true}
								path={route.path}
								component={(props) => {
									return <route.component {...props} />;
								}}
							/>
						))}
					</Switch>
					<UISiteFooter />
				</BrowserRouter>
			</ConfigProvider>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		isAuthenticated: state.auth.isAuthenticated,
	};
};

export default connect(mapStateToProps)(Main);
