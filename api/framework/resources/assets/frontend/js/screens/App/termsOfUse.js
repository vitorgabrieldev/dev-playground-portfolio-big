import React, { Component, Fragment } from "react";
import axios from "axios";

import {
	UIError,
	UIPageTitle,
	UISkeleton,
} from "./../../components";

import * as seo from "./../../helpers/seo";

import { API_ERRO_TYPE_CANCEL } from "./../../config/general";

import { institutionalService } from "./../../redux/services";

class AppTermsOfUse extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			hasError : false,
			error    : '',
			item     : null,
		};

		this._cancelToken = null;
	}

	componentDidMount() {
		document.body.classList.add('page-app');

		seo.setTitle('Termos de uso');

		this.fetchItem();
	}

	componentWillUnmount() {
		document.body.classList.remove('page-app');

		this._cancelToken && this._cancelToken.cancel("Landing Component got unmounted");
	}

	fetchItem = () => {
		this.setState({
			isLoading: true,
			hasError : false,
			error    : '',
		});

		this._cancelToken = axios.CancelToken.source();

		institutionalService.termsOfUse(this._cancelToken.token)
		.then((response) => {
			this.setState({
				isLoading: false,
				item     : response.data.data,
			});
		})
		.catch((data) => {
			if( data?.error_type === API_ERRO_TYPE_CANCEL ) return null;

			this.setState({
				isLoading: false,
				hasError : true,
				error    : String(data),
			});
		});
	}

	render() {
		const {isLoading, item} = this.state;

		return (
			<main id="site-main" role="main">
				<div className="container">
					<div className="main-content">
						{isLoading ? (
							<UISkeleton type="longtext" />
						) : (
							<Fragment>
								{this.state.hasError ? (
									<UIError text={this.state.error} onPressOk={this.fetchItem} />
								) : (
									<div dangerouslySetInnerHTML={{__html: item?.text}} />
								)}
							</Fragment>
						)}
					</div>
				</div>
			</main>
		)
	}
}

export default AppTermsOfUse;