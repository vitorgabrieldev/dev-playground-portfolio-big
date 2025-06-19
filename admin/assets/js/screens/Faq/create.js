import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, InputNumber, message, Modal, Switch, Select } from "antd";

import { faqService } from "./../../redux/services";

import {
	UIDrawerForm,
} from "./../../components";

const formId = `form-drawer-${Math.floor(Math.random() * 10001)}`;

class Create extends Component {
	static propTypes = {
		visible   : PropTypes.bool.isRequired,
		onComplete: PropTypes.func.isRequired,
		onClose   : PropTypes.func.isRequired,
	};

	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			isSending: false,
			nextOrder: 1,
		};
	}

	onOpen = () => {
		this.setState({
			isLoading: true,
		});

		faqService.getAdditionalInformation()
		.then((response) => {
			this.setState({
				isLoading: false,
				nextOrder: response.data.next_order,
			});
		})
		.catch((data) => {
			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
				onOk   : () => {
					// Force close
					return this.onClose();
				}
			});
		});
	};

	resetFields = () => {
	};

	onClose = () => {
		// Reset fields
		this.resetFields();

		// Callback
		this.props.onClose();
	};

	onFinish = (values) => {
		this.setState({
			isSending: true,
		});

		const data = {
			...values,
		};

		faqService.create(data)
		.then((response) => {
			this.setState({
				isSending: false,
			});

			// Reset fields
			this.resetFields();

			// Success message
			message.success("Registro cadastrado com sucesso.");

			// Callback
			this.props.onComplete();
		})
		.catch((data) => {
			this.setState({
				isSending: false,
			});

			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
			});
		});
	};

	render() {
		const {visible} = this.props;

		const {isLoading, isSending, nextOrder} = this.state;

		return (
			<UIDrawerForm
				visible={visible}
				width={500}
				onClose={this.onClose}
				isLoading={isLoading}
				isSending={isSending}
				formId={formId}
				title="Incluir novo registro">
				<Form
					ref={el => this.form = el}
					id={formId}
					layout="vertical"
					scrollToFirstError
					onFinish={this.onFinish}
					initialValues={{
						order    : nextOrder,
						is_active: true,
					}}>
					<Form.Item name="name" label="Texto da pergunta" hasFeedback rules={[{required: true, message: "Campo obrigatório."}]}>
						<Input maxLength={191} />
					</Form.Item>
					<Form.Item name="text" label="Texto da resposta" hasFeedback rules={[{required: true, message: "Campo obrigatório."}]}>
						<Input.TextArea autoSize={{minRows: 3, maxRows: 6}} />
					</Form.Item>
					<Form.Item name="order" label="Ordem">
						<InputNumber min={0} style={{width: '100%', maxWidth: 150}} />
					</Form.Item>
					<Form.Item name="is_active" label="Ativo" valuePropName="checked">
						<Switch />
					</Form.Item>
				</Form>
			</UIDrawerForm>
		)
	}
}

export default Create;
