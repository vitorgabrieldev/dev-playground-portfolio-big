import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber, Select, Spin } from "antd";
import axios from "axios";

import { ScreenProtectorsService } from "./../../redux/services";

import {
	UIDrawerForm,
	UIUpload,
	UIRichTextEditor,
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

		if (this.uploadCapa) {
			this.uploadCapa.reset();
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
		const fileCapa = this.uploadCapa.getFiles();

		this.setState({ isSending: true });
	
		const data = { ...values };

		data.is_active = data.is_active ? 1 : 0;

		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.file = file.files[0];
			}
		}

		if (fileCapa.files.length) {
			if (!fileCapa.files[0].uuid) {
				data.capa = fileCapa.files[0];
			}
		}
	
		ScreenProtectorsService.create(data)
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

					<UIUpload
						ref={el => this.uploadCapa = el}
						label="Imagem de capa"
						labelError="capa"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={1}
						minFiles={1}
					/>

					<UIUpload
						ref={el => this.upload = el}
						label="Arquivo do protetor de tela"
						labelError="protetor de tela"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={1}
						minFiles={1}
					/>

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
