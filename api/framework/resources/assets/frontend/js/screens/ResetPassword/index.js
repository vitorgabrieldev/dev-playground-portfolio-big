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

import { ADMIN_URL, API_ERRO_TYPE_CANCEL } from "./../../config/general";

import { generalService } from "./../../redux/services";

class ResetPassword extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isSending: false,
			hasSent  : false,
		};

		this._cancelTokenSend = null;
	}

	componentDidMount() {
		document.body.classList.add('page-reset-password');

		seo.setTitle('Redefinir senha');
	}

	componentWillUnmount() {
		document.body.classList.remove('page-recovery-password');

		this._cancelTokenSend && this._cancelTokenSend.cancel("Landing Component got unmounted");
	}

	onFinish = (values) => {
		this.setState({
			isSending: true,
		});

		this._cancelTokenSend = axios.CancelToken.source();

		const data = {
			...values,
			type : this.props.match.params?.type,
			token: this.props.match.params?.token,
		};

		generalService.passwordReset(data, this._cancelTokenSend.token)
		.then((response) => {
			this.setState({
				isSending: false,
				hasSent  : true,
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
	};

	render() {
		const {isSending, hasSent} = this.state;

		const getParams = new URLSearchParams(this.props.location.search);

		const type = this.props.match.params?.type;

		return (
			<main id="site-main" role="main" className="site-main-padding-top-medium">
				<div className="container">
					<div className="main-content">
						<Row justify="center">
							<Col xs={24} sm={14} md={12} lg={8}>
								{hasSent ? (
									<Fragment>
										<UIPageTitle
											title="Sua senha foi redefinida!"
											subtitle={`Você já pode acessar sua conta com sua nova senha.`}
											className="mb-40 mb-md-60 text-center"
											subtitleClass="mt-md-10"
										/>
										{type === 'users' && (
											<a href={ADMIN_URL}>
												<Button type="primary" block>Ir para o painel</Button>
											</a>
										)}
									</Fragment>
								) : (
									<Fragment>
										<UIPageTitle
											title="Redefinir senha"
											className="mb-40 mb-md-60 text-center"
										/>
										<Form
											layout="vertical"
											scrollToFirstError
											onFinish={this.onFinish}
											className="mb-50 mb-sm-100"
											initialValues={{
												email: getParams.get('email') ?? '',
											}}>
											<Form.Item name="email" rules={[{required: true, message: "Campo obrigatório."}, {type: "email", message: "Informe um e-mail válido."}]}>
												<UIFieldInput disabled={isSending} placeholder="E-mail" />
											</Form.Item>
											<Form.Item
												name="password"
												rules={[
													{required: true, message: "Campo obrigatório."},
													{min: 6, message: "Deve conter no mínimo 6 caracteres."},
												]}>
												<UIFieldInputPassword
													disabled={isSending}
													placeholder="Nova senha"
													className="input-password-icon-text"
													iconRender={visible => (visible ? "Ocultar" : "Exibir")}
												/>
											</Form.Item>
											<Form.Item
												name="password_confirmation"
												dependencies={['password']}
												rules={[
													{required: true, message: "Campo obrigatório."},
													({getFieldValue}) => ({
														validator(rule, value) {
															if( !value || getFieldValue('password') === value )
															{
																return Promise.resolve();
															}

															return Promise.reject("Deve conter o mesmo valor de Nova senha.");
														},
													}),
												]}>
												<UIFieldInputPassword
													disabled={isSending}
													placeholder="Confirmar nova senha"
													className="input-password-icon-text"
													iconRender={visible => (visible ? "Ocultar" : "Exibir")}
												/>
											</Form.Item>
											<Button type="primary" htmlType="submit" className="mt-10" block loading={isSending}>Redefinir senha</Button>
										</Form>
									</Fragment>
								)}
							</Col>
						</Row>
					</div>
				</div>
			</main>
		)
	}
}

export default ResetPassword;
