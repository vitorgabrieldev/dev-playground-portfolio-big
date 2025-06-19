import React, { Component, Fragment } from "react";
import axios from "axios";
import { Button, Form, message, Modal, Spin } from "antd";
import QueueAnim from "rc-queue-anim";

import { termOfUseService } from "./../../redux/services";
import { API_ERRO_TYPE_CANCEL } from "./../../config/general";
import { UIRichTextEditor } from "./../../components";

class TermOfUse extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			isSending: false,
			terms: null
		};

		this._cancelToken = null;
	}

	componentDidMount() {
		this._cancelToken = axios.CancelToken.source();

		termOfUseService
			.show(this._cancelToken.token)
			.then((response) => {
				this.setState({ isLoading: false });
				this.fillForm(response.data.data);
			})
			.catch((data) => {
				if (data.error_type === API_ERRO_TYPE_CANCEL) return;

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	}

	componentWillUnmount() {
		this._cancelToken && this._cancelToken.cancel("Landing Component got unmounted");
	}

	fillForm = (data) => {
		if (data && Array.isArray(data) && data.length > 0) {
			const item = data[0];
			this.customer && this.customer.setValue(item.text);
			this.setState({ terms: { uuid: item.uuid } });
		}
	};

	onFinish = (values) => {
		this.setState({ isSending: true });

		const data = {
			uuid: this.state.terms?.uuid,
			text: values.customer
		};

		termOfUseService.edit(data)
			.then(() => {
				this.setState({ isSending: false });
				message.success("Registro atualizado com sucesso.");
			})
			.catch((err) => {
				this.setState({ isSending: false });
				Modal.error({
					title: "Ocorreu um erro!",
					content: String(err),
				});
			});
	};

	render() {
		const { isLoading, isSending } = this.state;

		return (
			<QueueAnim className="site-content-inner" style={{ maxWidth: 700 }}>
				<div className="page-content" key="1">
					<h1 className="page-title">Termos de Uso</h1>
					<Form
						ref={el => this.form = el}
						layout="vertical"
						scrollToFirstError
						onFinish={this.onFinish}
						initialValues={{ reset_accept: 0 }}
					>
						{isLoading ? (
							<div className="text-center" style={{ padding: 20 }}>
								<Spin indicator={<i className="fad fa-spinner-third fa-spin fa-3x" />} />
							</div>
						) : (
							<Fragment>
								<UIRichTextEditor
									ref={el => this.customer = el}
									name="customer"
									label="Texto"
									required={true}
								/>
								<Button
									type="primary"
									htmlType="submit"
									icon={<i className="far fa-check" />}
									loading={isSending}
									disabled={isLoading}
								>
									{isSending ? "Salvando" : "Salvar"}
								</Button>
							</Fragment>
						)}
					</Form>
				</div>
			</QueueAnim>
		);
	}
}

export default TermOfUse;
