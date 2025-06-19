import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Input, message, Modal, Switch, InputNumber, Upload, Button } from "antd";
import { PlusOutlined } from '@ant-design/icons';
import moment from "moment";

import { contentsSeeMoreService } from "./../../redux/services";

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
			imageList: [],
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
		if (this.coverUpload) {
			this.coverUpload.reset();
		}
		this.setState({ imageList: [] });
	};

	onClose = () => {
		// Reset fields
		this.resetFields();

		// Callback
		this.props.onClose();
	};

	handleImageChange = ({ fileList }) => {
		this.setState({ imageList: fileList });
	};

	onFinish = (values) => {
		const icon = this.upload.getFiles();
		const cover = this.coverUpload.getFiles();
		const { imageList } = this.state;
	
		if (icon.hasError || cover.hasError) {
			Modal.error({
				title: "Ocorreu um erro!",
				content: icon.error || cover.error,
			});
			return false;
		}

		if (!icon.files.length) {
			Modal.error({
				title: "Ocorreu um erro!",
				content: "É necessário enviar um ícone.",
			});
			return false;
		}
	
		this.setState({ isSending: true });
	
		const data = { ...values };
		data.is_active = values.is_active ? 1 : 0;
	
		// Handle icon
		if (icon.files.length) {
			data.icone = icon.files[0];
		}

		// Handle cover
		if (cover.files.length) {
			data.capa = cover.files[0];
		}

		// Handle images and captions
		if (imageList.length) {
			const imagens = [];
			const legendas = [];
			
			imageList.forEach(img => {
				imagens.push(img.originFileObj);
				legendas.push(values[`caption_${img.uid}`] || '');
			});
			
			data.imagens = imagens;
			data.legenda = legendas;
		}

		if (!data.video) {
			delete data.video;
		}

		contentsSeeMoreService.create(data)
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
		const {isLoading, isSending, nextOrder, imageList} = this.state;

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

					<UIUpload
						ref={el => this.upload = el}
						label="Ícone"
						labelError="Ícone"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						minFiles={1}
						maxFiles={1}
					/>

					<Form.Item 
						name="name" 
						label="Título" 
						rules={[{required: true, message: "Campo obrigatório."}]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<UIUpload
						ref={el => this.coverUpload = el}
						label="Imagem de capa"
						labelError="Imagem de capa"
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
						required={true}
					/>

					<Form.Item 
						label="Imagens"
					>
						<div style={{ marginBottom: 16 }}>
							<Upload
								listType="picture-card"
								fileList={imageList}
								onChange={this.handleImageChange}
								beforeUpload={() => false}
								accept=".png,.jpg,.jpeg"
								itemRender={(originNode, file) => (
									<div style={{ position: 'relative' }}>
										{originNode}
									</div>
								)}
							>
								{imageList.length >= 10 ? null : (
									<div style={{ 
										display: 'flex', 
										flexDirection: 'column', 
										alignItems: 'center', 
										justifyContent: 'center',
										height: '100%'
									}}>
										<PlusOutlined style={{ fontSize: '24px', color: '#999' }} />
										<div style={{ marginTop: 8, color: '#666' }}>Adicionar imagem</div>
									</div>
								)}
							</Upload>
						</div>

						{imageList.length > 0 && (
							<div style={{ 
								marginTop: 16,
								padding: 16,
								background: '#fafafa',
								borderRadius: 4,
								border: '1px solid #f0f0f0'
							}}>
								<div style={{ 
									marginBottom: 16, 
									fontWeight: 500,
									color: '#666'
								}}>
									Legendas das imagens
								</div>
								{imageList.map(file => (
									<div key={file.uid} style={{ marginBottom: 16 }}>
										<div style={{ 
											display: 'flex', 
											alignItems: 'center',
											marginBottom: 8
										}}>
											<img 
												src={file.thumbUrl || file.url || URL.createObjectURL(file.originFileObj)} 
												alt={file.name}
												style={{ 
													width: 40, 
													height: 40, 
													objectFit: 'cover',
													marginRight: 8,
													borderRadius: 4
												}} 
											/>
											<span style={{ color: '#666' }}>{file.name}</span>
										</div>
										<Form.Item
											name={`caption_${file.uid}`}
											noStyle
										>
											<Input.TextArea 
												maxLength={500} 
												autoSize={{minRows: 2, maxRows: 4}}
												placeholder="Digite a legenda para esta imagem"
												style={{ width: '100%' }}
											/>
										</Form.Item>
									</div>
								))}
							</div>
						)}
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
