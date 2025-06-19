import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, DatePicker } from "antd";
import moment from "moment";
import { NotificationsService } from "./../../redux/services";

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

		NotificationsService.show({uuid})
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
			name      		: data.name,
			descricao   	: data.descricao,
			video      		: data.video,
			titulo_button   : data.titulo_button,
			link_button     : data.link_button,
			data_envio      : moment(data.data_envio),
			send_push 		: data.send_push,
		});

		this.editor.setValue(data?.text ?? null);

		if( data.capa )
		{
			this.upload.setFiles([
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

		// Callback
		this.props.onClose();
	};

	onFinish = (values) => {
		const capa = this.upload.getFiles();

		this.setState({ isSending: true });
	
		const data = { ...values };

		data.uuid = this.state.uuid;
		data.is_active = data.is_active ? 1 : 0;

		if (capa.files.length) {
			if (!capa.files[0].uuid) {
				data.capa = capa.files[0];
			}
		}

		NotificationsService.edit(data)
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

export default Edit;
