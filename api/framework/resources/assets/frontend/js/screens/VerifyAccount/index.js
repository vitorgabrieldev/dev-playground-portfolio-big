import React, { Component, Fragment } from "react";
import axios from "axios";
import { Button, Col, Form, Row, Modal } from "antd";

import {
	UIFieldInput,
	UIFieldInputPassword,
	UILink,
	UIPageTitle,
} from "./../../components";

import * as seo from "./../../helpers/seo";

import { SITE_URL, API_ERRO_TYPE_CANCEL } from "./../../config/general";

import { generalService } from "./../../redux/services";

class VerifyAccount extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isSending: false,
			hasValidate  : false,
		};

		this._cancelTokenSend = null;
	}

	componentDidMount() {
		document.body.classList.add('page-reset-password');

		seo.setTitle('Conta verificada');

		this.setState({
			isSending: true,
		});

		this._cancelTokenSend = axios.CancelToken.source();

		const data = {
			token: this.props.match.params?.token,
		};

		generalService.verifyAccount(data, this._cancelTokenSend.token)
		.then((response) => {
			this.setState({
				isSending: false,
				hasValidate  : true,
			});
		})
		.catch((data) => {
			if( data?.error_type === API_ERRO_TYPE_CANCEL ) return null;

			this.setState({
				isSending: false,
			});

			Modal.error({
				title   : "Ocorreu um erro!",
				content : String(data),
				centered: true,
			});
		});
	}

	componentWillUnmount() {
		document.body.classList.remove('page-recovery-password');

		this._cancelTokenSend && this._cancelTokenSend.cancel("Landing Component got unmounted");
	}

	render() {
		const {isSending, hasValidate} = this.state;

		const getParams = new URLSearchParams(this.props.location.search);

		const type = this.props.match.params?.type;

		return (
			<main id="site-main" role="main" className="site-main-padding-top-medium">
				<div className="container">
					<div className="main-content">
						<Row justify="center">
							<Col xs={24} sm={14} md={12} lg={8}>
								<Fragment>
									<UIPageTitle
										title={hasValidate ? "Conta verificada!" : "Verificando conta ..."}
										subtitle={hasValidate ? "Você já pode realizar o seu login." : "Aguarde"}
										className="mb-40 mb-md-60 text-center"
										subtitleClass="mt-md-10"
									/>
									<a href={SITE_URL}>
										<Button type="primary" loading={!hasValidate} block>Ir para o site</Button>
									</a>
								</Fragment>
							</Col>
						</Row>
					</div>
				</div>
			</main>
		)
	}
}

export default VerifyAccount;