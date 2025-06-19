import React, { Component } from "react";
import axios from "axios";
import { Button, Col, Form, Input, message, Modal, Row, Spin } from "antd";
import QueueAnim from "rc-queue-anim";

import { API_ERRO_TYPE_CANCEL } from "./../../config/general";
import { onboardingService } from "./../../redux/services";
import { UIUpload } from "./../../components";

class Onboarding extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			isSending: false,
			upload: null,
		};

		this._cancelToken = null;
	}

	componentDidMount() {
		this._cancelToken = axios.CancelToken.source();

		onboardingService.getOnboarding(this._cancelToken.token)
			.then((response) => {
				this.setState({ isLoading: false });
				this.fillForm(response.data.data);
			})
			.catch((data) => {
				if (data?.error_type === API_ERRO_TYPE_CANCEL) return null;

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	}

	componentWillUnmount() {
		this._cancelToken && this._cancelToken.cancel("Componente desmontado");
	}

	fillForm = (data) => {
		this.form.setFieldsValue({
			frase: data.frase,
			frase2: data.frase2,
		});

		if (data.file) {
			this.upload.setFiles([{
				uuid: data.uuid,
				url: data.file,
				type: 'image/jpeg',
			}]);
		}
	};

	onFinish = (values) => {
		const file = this.upload.getFiles();

		if (file.hasError) {
			Modal.error({
				title: "Ocorreu um erro!",
				content: file.error,
			});
			return false;
		}

		this.setState({ isSending: true });

		const data = { ...values };

		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.file = file.files[0];
			}
		} else {
			data.file = null;
		}

		onboardingService.edit(data)
			.then(() => {
				this.setState({ isSending: false });
				message.success("Registro atualizado com sucesso.");
			})
			.catch((data) => {
				this.setState({ isSending: false });

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	render() {
		const { isLoading, isSending } = this.state;

		return (
			<QueueAnim className="site-content-inner" style={{ maxWidth: 700 }}>
				<div className="page-content" key="1">
					<h1 className="page-title">Onboarding</h1>
					<Form
						ref={el => this.form = el}
						layout="vertical"
						scrollToFirstError
						onFinish={this.onFinish}>
						{isLoading ? (
							<div className="text-center" style={{ padding: 20 }}>
								<Spin indicator={<i className="fad fa-spinner-third fa-spin fa-3x" />} />
							</div>
						) : (
							<Row gutter={16}>
								<Col xs={24}>
									<Form.Item
										name="frase"
										label="Título"
										hasFeedback
										rules={[{ required: true, message: "Campo obrigatório." }]}>
										<Input />
									</Form.Item>
									<Form.Item
										name="frase2"
										label="Frase"
										hasFeedback
										rules={[{ required: true, message: "Campo obrigatório." }]}>
										<Input />
									</Form.Item>
									<UIUpload
										ref={el => this.upload = el}
										label="Imagem"
										labelError="Imagem"
										acceptedFiles={['png', 'jpg', 'jpeg']}
										maxFiles={1}
										minFiles={1}
									/>
									<Button
										type="primary"
										htmlType="submit"
										icon={<i className="far fa-check" />}
										loading={isSending}
										disabled={isLoading}>
										{isSending ? "Salvando" : "Salvar"}
									</Button>
								</Col>
							</Row>
						)}
					</Form>
				</div>
			</QueueAnim>
		)
	}
}

export default Onboarding;