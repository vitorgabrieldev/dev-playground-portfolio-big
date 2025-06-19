import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber, Select, Spin, Row, Col } from "antd";
import axios from "axios";

import { TrainingsService, categoriesTrainingService } from "./../../redux/services";

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

		TrainingsService.show({uuid})
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
			duracao      : data.duracao,
			video      : data.video,
			destaque   : Boolean(data.destaque),
			order     : data.order,
			is_active  : Boolean(data.is_active),
			categoria_id: data.categoria?.uuid || null,
		});

		this.editor.setValue(data?.descricao ?? null);

		if (data.categoria)
		{
			let categoria = data.categoria;
			this.setState({
				categorias: [{
					name: categoria.name,
					uuid: categoria.uuid
				}]
			});
		}

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
		const file = this.upload.getFiles();

		this.setState({ isSending: true });
	
		const data = { ...values };
		data.uuid = this.state.uuid;
		data.is_active = values.is_active ? 1 : 0;
		data.destaque = values.destaque ? 1 : 0;

		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.capa = file.files[0];
			}
		}

		TrainingsService.edit(data)
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

	fetchCategorias = (value) => {
		if (this._axiosCancelCategoriasToken) {
			this._axiosCancelCategoriasToken.cancel("Only one request allowed at a time.");
		}

		this._axiosCancelCategoriasToken = axios.CancelToken.source();

		this.setState({
			categoriaIsLoading: true,
		});

		categoriesTrainingService.getAutocomplete({
			search: value,
			cancelToken: this._axiosCancelCategoriasToken.token,
		})
			.then((response) => {
				this.setState({
					categoriaIsLoading: false,
					categorias: response.data.data,
				});
			})
			.catch((data) => {
				if (data.error_type === API_ERRO_TYPE_CANCEL) return null;

				this.setState({
					categoriaIsLoading: false,
				});

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	render() {
		const {visible} = this.props;
		const {isLoading, isSending, categoriaIsLoading, categorias} = this.state;

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

					<Form.Item name="categoria_id" label="Categoria" rules={[{required: true, message: "Campo obrigatório."}]}>
						<Select
							filterOption={false}
							allowClear
							notFoundContent={categoriaIsLoading ? <Spin indicator={<i className="fad fa-spinner-third fa-spin" />} /> : null}
							onSearch={this.fetchCategorias}
							showSearch
							onDropdownVisibleChange={visible => {
								if (visible && !categorias.length) {
									this.fetchCategorias('');
								}
							}}
							options={categorias.map((item, index) => ({
								label: item.name,
								value: item.uuid
							}))}
						/>
					</Form.Item>

					<Form.Item 
						name="name" 
						label="Título" 
						rules={[{required: true, message: "Campo obrigatório."}]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<UIUpload
						ref={el => this.upload = el}
						label="Imagem de capa"
						labelError="imagem de capa"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={1}
						minFiles={1}
					/>

					<Form.Item 
						name="video" 
						label="Vídeo" 
						rules={[{required: true, message: "Campo obrigatório."}]}
					>
						<Input/>
					</Form.Item>

					<Form.Item 
						name="duracao" 
						label="Duração" 
					>
						<Input/>
					</Form.Item>

					<UIRichTextEditor
						ref={el => this.editor = el}
						name="descricao"
						label="Descrição"
					/>

					<Form.Item 
						name="order" 
						label="Ordem"
					>
						<InputNumber min={0} style={{width: '100%'}} />
					</Form.Item>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item 
								name="destaque" 
								label="Destaque"
								valuePropName="checked"
							>
								<Switch />
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item 
								name="is_active" 
								label="Ativo" 
								valuePropName="checked"
							>
								<Switch />
							</Form.Item>
						</Col>
					</Row>
				</Form>
			</UIDrawerForm>
		)
	}
}

export default Edit;
