import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber, DatePicker } from "antd";

import { NotificationsService } from "./../../redux/services";

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
	};

	onClose = () => {
		// Reset fields
		this.resetFields();

		// Callback
		this.props.onClose();
	};

	onFinish = (values) => {
		const capa = this.upload.getFiles();

		this.setState({ isSending: true });
	
		const data = { ...values };

		data.is_active = data.is_active ? 1 : 0;

		if (capa.files.length) {
			if (!capa.files[0].uuid) {
				data.capa = capa.files[0];
			}
		}

		NotificationsService.create(data)
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
						send_push: true,
					}}>

					<Form.Item 
						name="name" 
						label="Título" 
						rules={[{required: true, message: "Campo obrigatório."}]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<Form.Item 
						name="descricao" 
						label="Descrição" 
					>
						<Input.TextArea maxLength={191} autoSize={{minRows: 3, maxRows: 6}} />
					</Form.Item>

					<UIUpload
						ref={el => this.upload = el}
						label="Imagem de capa"
						labelError="capa"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={1}
					/>

					<Form.Item 
						name="video" 
						label="Vídeo" 
					>
						<Input maxLength={191} />
					</Form.Item>

					<UIRichTextEditor
						ref={el => this.editor = el}
						name="text"
						label="Texto"
					/>

					<Form.Item 
						name="titulo_button" 
						label="Título do botão" 
					>
						<Input maxLength={191} />
					</Form.Item>

					<Form.Item 
						name="link_button" 
						label="Link do botão" 
						rules={[{ type: 'url', message: 'Insira uma URL válida.' }]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<Form.Item name="data_envio" label="Data e hora" rules={[{required: true, message: "Campo obrigatório."}]}>
						<DatePicker
							showTime
							format="DD/MM/YYYY HH:mm"
							style={{width: "100%"}}
						/>
					</Form.Item>

					<Form.Item 
						name="send_push" 
						label="Enviar push?" 
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
