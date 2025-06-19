import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber, Select, Spin, Row, Col } from "antd";
import axios from "axios";

import { categoriesTrainingService, TrainingsService } from "./../../redux/services";

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
			categoriaIsLoading: false,
			categorias: [],
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

		this.setState({ isSending: true });
	
		const data = { ...values };

		data.is_active = data.is_active ? 1 : 0;
		data.destaque = data.destaque ? 1 : 0;

		if (file.files.length) {
			if (!file.files[0].uuid) {
				data.capa = file.files[0];
			}
		}
	
		TrainingsService.create(data)
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
		const {isLoading, isSending, nextOrder, categoriaIsLoading, categorias} = this.state;

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

					<Form.Item name="categoria_treinamento_id" label="Categoria" rules={[{required: true, message: "Campo obrigatório."}]}>
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

export default Create;
