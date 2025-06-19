import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, DatePicker, Switch } from "antd";
import moment from "moment";

import { newsService } from "./../../redux/services";

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
		data.data_inicio = values.data_inicio.format("YYYY-MM-DDTHH:mm:ssZ");
	
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

		newsService.create(data)
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
						ref={el => this.upload = el}
						label="Imagens"
						labelError="imagens"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={10}
						minFiles={1}
					/>

					<Form.Item 
						name="sinopse" 
						label="Sinopse"
					>
						<Input.TextArea maxLength={500} autoSize={{minRows: 3, maxRows: 6}} />
					</Form.Item>

					<UIRichTextEditor
						ref={el => this.editor = el}
						name="text"
						label="Texto"
						required={true}
					/>

					<Form.Item name="data_inicio" label="Data" rules={[{required: true, message: "Campo obrigatório."}]}>
						<DatePicker
							showTime
							format="DD/MM/YYYY HH:mm"
							style={{width: "100%"}}
						/>
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
