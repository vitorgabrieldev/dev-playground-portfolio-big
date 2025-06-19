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

class Edit extends Component {
	static propTypes = {
		visible   : PropTypes.bool.isRequired,
		onComplete: PropTypes.func.isRequired,
		onClose   : PropTypes.func.isRequired,
	};

	constructor(props) {
		super(props);

		this.state = {
			uuid     : 0,
			isLoading: true,
			isSending: false,
		};
	}

	onOpen = (uuid) => {
		this.setState({
			isLoading: true,
			uuid     : uuid,
		});

		bannersService.show({uuid})
		.then((response) => {
			const item = response.data.data;
			this.setState({
				isLoading: false,
			}, () => {
				// Fill form
				this.fillForm(item);
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

	fillForm = (data) => {
		this.form.setFieldsValue({
			name      : data.name,
			frase     : data.frase,
			link      : data.link,
			order     : data.order,
			is_active : data.is_active,
		});

		if( data.file )
		{
			this.upload.setFiles([
				{
					uuid: data.uuid,
					url : data.file,
					type: 'image/jpeg',
				}
			]);
		}
	};

	onClose = () => {
		// Reset fields
		if (this.upload) {
			this.upload.reset();
		}

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
		data.uuid = this.state.uuid;
		data.is_active = values.is_active ? 1 : 0;
	
		// File - Desktop
		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.file = file.files[0];
			}
		}

		bannersService.edit(data)
		.then((response) => {
			this.setState({
				isSending: false,
			});

			// Success message
			message.success("Registro atualizado com sucesso.");

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
		const {isLoading, isSending} = this.state;

		return (
			<UIDrawerForm
				visible={visible}
				width={500}
				onClose={this.onClose}
				isLoading={isLoading}
				isSending={isSending}
				formId={formId}
				title="Editar registro">
				<Form
					ref={el => this.form = el}
					id={formId}
					layout="vertical"
					scrollToFirstError
					onFinish={this.onFinish}>

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

export default Edit;
