import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber } from "antd";

import { ScreenProtectorsService } from "./../../redux/services";

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
			categoriaIsLoading: false,
			categorias: [],
		};
	}

	onOpen = (uuid) => {
		this.setState({
			isLoading: true,
			uuid     : uuid,
		});

		ScreenProtectorsService.show({uuid})
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
			order     : data.order,
			is_active  : Boolean(data.is_active),
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

		if( data.capa )
		{
			this.uploadCapa.setFiles([
				{
					uuid: data.uuid,
					url : data.capa,
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

		if (this.uploadCapa) {
			this.uploadCapa.reset();
		}

		// Callback
		this.props.onClose();
	};

	onFinish = (values) => {
		const file = this.upload.getFiles();
		const fileCapa = this.uploadCapa.getFiles();

		if (file.hasError) {
			Modal.error({
				title: "Ocorreu um erro!",
				content: file.error,
			});

			return false;
		}

		if (fileCapa.hasError) {
			Modal.error({
				title: "Ocorreu um erro!",
				content: fileCapa.error,
			});

			return false;
		}

		this.setState({ isSending: true });

		const data = { ...values };
		data.uuid = this.state.uuid;

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

		ScreenProtectorsService.edit(data)
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

export default Edit;
