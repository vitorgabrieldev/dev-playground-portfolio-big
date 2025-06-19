import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber } from "antd";
import axios from "axios";

import { bannersService } from "./../../redux/services";

import {
	UIDrawerForm,
	UIUpload,
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
			isLoading: false,
		});
	};

	resetFields = () => {
		if (this.upload) {
			this.upload.reset();
		}
	};

	onClose = () => {
		// Reset fields
		this.resetFields();

		// Callback
		this.props.onClose();
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
		data.is_active = values.is_active ? 1 : 0;
	
		// File
		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.file = file.files[0];
			}
		}

		bannersService.create(data)
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
				title="Incluir registro">
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

					<Form.Item 
						name="name" 
						label="Título" 
						rules={[{required: true, message: "Campo obrigatório."}]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<Form.Item 
						name="frase" 
						label="Frase"
					>
						<Input.TextArea maxLength={500} autoSize={{minRows: 3, maxRows: 6}} />
					</Form.Item>

					<UIUpload
						ref={el => this.upload = el}
						label="Imagem"
						labelError="Imagem"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={1}
						minFiles={1}
					/>

					<Form.Item 
						name="link" 
						label="Link externo"
						rules={[{type: 'url', message: 'Digite uma URL válida'}]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<Form.Item 
						name="order" 
						label="Ordem"
					>
						<InputNumber min={0} style={{width: '100%'}} />
					</Form.Item>

					<Form.Item 
						name="is_active" 
						label="Ativo" 
						valuePropName="checked"
					>
						<Switch />
					</Form.Item>
				</Form>
			</UIDrawerForm>
		)
	}
}

export default Create;
