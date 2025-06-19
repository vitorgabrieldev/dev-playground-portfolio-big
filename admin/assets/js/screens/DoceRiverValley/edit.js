import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber } from "antd";

import { DoceRiverValleyService } from "./../../redux/services";

import {
	UIDrawerForm,
	UIUpload,
	UIRichTextEditor,
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

		DoceRiverValleyService.show({uuid})
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
			sinopse   : data.sinopse,
			order     : data.order,
			is_active : data.is_active,
		});

		this.editor.setValue(data?.text ?? null);

		if( data.media )
		{
			let images = [];
			data?.media.forEach(imgs => {
				images.push({
					uuid: imgs.uuid,
					url : imgs.file,
					type: 'image/jpeg',
					name: imgs.file.split('/').pop() || 'image.jpg'
				});
			});
			this.upload.setFiles(images);
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

		this.setState({ isSending: true });
	
		const data = { ...values };

		data.uuid = this.state.uuid;
		data.is_active = data.is_active ? 1 : 0;

		// Handle images
		if (file.files.length) {
			let imagens = [];
			let imagens_no_delete = [];
			
			file.files.forEach(img => {
				if (!img.uuid) {
					imagens.push(img);
				} else {
					imagens_no_delete.push(img.uuid);
				}
			});
			
			data.imagens = imagens;
			data.imagens_no_delete = imagens_no_delete;
		}

		DoceRiverValleyService.edit(data)
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
						ref={el => this.upload = el}
						label="Imagens"
						labelError="protetor de tela"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={10}
						minFiles={1}
					/>

					<Form.Item 
						name="sinopse" 
						label="Sinopse" 
					>
						<Input maxLength={5000} />
					</Form.Item>

					<UIRichTextEditor
						ref={el => this.editor = el}
						name="text"
						label="Texto"
						required={true}
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
