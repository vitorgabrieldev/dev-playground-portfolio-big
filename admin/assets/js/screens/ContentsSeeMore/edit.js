import React, { Component } from "react";
import * as PropTypes from "prop-types";
import {
	Form,
	Input,
	message,
	Modal,
	Switch,
	InputNumber,
	Upload,
	Button,
} from "antd";
import { PlusOutlined } from "@ant-design/icons";
import moment from "moment";

import { contentsSeeMoreService } from "./../../redux/services";

import { UIDrawerForm, UIUpload, UIRichTextEditor } from "./../../components";

const formId = `form-drawer-${Math.floor(Math.random() * 10001)}`;

class Edit extends Component {
	static propTypes = {
		visible: PropTypes.bool.isRequired,
		onComplete: PropTypes.func.isRequired,
		onClose: PropTypes.func.isRequired,
	};

	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			isSending: false,
			imageList: [],
			item: null,
			formInitialized: false,
		};
	}

	onOpen = (uuid) => {
		this.setState({
			isLoading: true,
		});

		contentsSeeMoreService
			.show({ uuid })
			.then((response) => {
				const item = response.data.data;

				// Handle images
				if (item.media && item.media.length) {
					const imageList = item.media.map((img) => ({
						uid: img.uuid,
						name: img.name,
						status: "done",
						url: img.file,
						thumbUrl: img.file_sizes.admin_listing,
						uuid: img.uuid,
						caption: img.description || "",
					}));

					this.setState({ imageList });
				}

				this.setState({
					isLoading: false,
					item,
				});

				this.fillForm(item);
			})
			.catch((data) => {
				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	fillForm = (data) => {
		if (this.state.formInitialized && this.state.item) {
			const item = this.state.item;

			this.form.setFieldsValue({
				name: item.name,
				text: item.text,
				video: item.video,
				order: item.order,
				is_active: item.is_active,
			});

			// Set editor value
			if (this.editor) {
				this.editor.setValue(item.text);
			}

			// Set icon and cover values
			if (this.upload && item.icone) {
				this.upload.setFiles([
					{
						uid: item.uuid,
						url: item.icone,
						type: "image/jpeg",
					},
				]);
			}

			if (this.coverUpload && item.capa) {
				this.coverUpload.setFiles([
					{
						uid: item.uuid,
						url: item.capa,
						type: "image/jpeg",
					},
				]);
			}
		}
	};

	resetFields = () => {
		if (this.upload) {
			this.upload.reset();
		}
		if (this.coverUpload) {
			this.coverUpload.reset();
		}
		this.setState({
			imageList: [],
			item: null,
			formInitialized: false,
		});
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

		this.setState({ isSending: true });

		const { imageList, item } = this.state;

		const data = { ...values };

		data.is_active = values.is_active ? 1 : 0;
		data.uuid = item.uuid;

		if (icon.files.length) {
			const file = icon.files[0];
			if (file.status !== "done") {
				data.icone = file;
			}
		} else {
			data.delete_icone = true;
		}

		if (cover.files.length) {
			const file = cover.files[0];
			if (file.status !== "done") {
				data.capa = file;
			}
		} else {
			data.delete_capa = true;
		}

		if (imageList.length) {
			const imagens = [];
			const legendas = [];
			const legenda_update = [];
			const uuid_legenda_update = [];
			const imagens_no_delete = [];
		
			imageList.forEach((img) => {
				const caption = values[`caption_${img.uid}`] || "";
		
				if (!img.uuid) {
					imagens.push(img.originFileObj);
					legendas.push(caption);
				} else {
					if (caption !== img.caption) {
						legenda_update.push(caption);
						uuid_legenda_update.push(img.uuid);
					};

					imagens_no_delete.push(img.uuid);
				}
			});
		
			data.imagens = imagens;
			data.legenda = legendas;
			data.legenda_update = legenda_update;
			data.imagens_update = uuid_legenda_update;
			data.imagens_no_delete = imagens_no_delete;
		}

		if (!data.video) {
			delete data.video;
		}

		contentsSeeMoreService
			.edit(data)
			.then((response) => {
				this.setState({
					isSending: false,
				});

				// Reset fields
				this.resetFields();

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
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	render() {
		const { visible } = this.props;
		const { isLoading, isSending, imageList } = this.state;

		return (
			<UIDrawerForm
				visible={visible}
				width={500}
				onClose={this.onClose}
				isLoading={isLoading}
				isSending={isSending}
				formId={formId}
				title="Editar registro"
			>
				<Form
					ref={(el) => {
						this.form = el;
						if (el && !this.state.formInitialized) {
							this.setState({ formInitialized: true });
						}
					}}
					id={formId}
					layout="vertical"
					scrollToFirstError
					onFinish={this.onFinish}
				>
					<UIUpload
						ref={(el) => (this.upload = el)}
						label="Ícone"
						labelError="Ícone"
						acceptedFiles={["png", "jpg", "jpeg"]}
						maxFiles={1}
					/>

					<Form.Item
						name="name"
						label="Título"
						rules={[{ required: true, message: "Campo obrigatório." }]}
					>
						<Input maxLength={191} />
					</Form.Item>

					<UIUpload
						ref={(el) => (this.coverUpload = el)}
						label="Imagem de capa"
						labelError="Imagem de capa"
						acceptedFiles={["png", "jpg", "jpeg"]}
						maxFiles={1}
					/>

					<Form.Item name="video" label="Vídeo">
						<Input maxLength={191} />
					</Form.Item>

					<UIRichTextEditor
						ref={(el) => (this.editor = el)}
						name="text"
						label="Texto"
						required={true}
					/>

					<Form.Item label="Imagens">
						<div style={{ marginBottom: 16 }}>
							<Upload
								listType="picture-card"
								fileList={imageList}
								onChange={this.handleImageChange}
								beforeUpload={() => false}
								accept=".png,.jpg,.jpeg"
								itemRender={(originNode, file) => (
									<div style={{ position: "relative" }}>{originNode}</div>
								)}
							>
								{imageList.length >= 10 ? null : (
									<div
										style={{
											display: "flex",
											flexDirection: "column",
											alignItems: "center",
											justifyContent: "center",
											height: "100%",
										}}
									>
										<PlusOutlined style={{ fontSize: "24px", color: "#999" }} />
										<div style={{ marginTop: 8, color: "#666" }}>
											Adicionar imagem
										</div>
									</div>
								)}
							</Upload>
						</div>

						{imageList.length > 0 && (
							<div
								style={{
									marginTop: 16,
									padding: 16,
									background: "#fafafa",
									borderRadius: 4,
									border: "1px solid #f0f0f0",
								}}
							>
								<div
									style={{
										marginBottom: 16,
										fontWeight: 500,
										color: "#666",
									}}
								>
									Legendas das imagens
								</div>
								{imageList.map((file) => (
									<div key={file.uid} style={{ marginBottom: 16 }}>
										<div
											style={{
												display: "flex",
												alignItems: "center",
												marginBottom: 8,
											}}
										>
											<img
												src={
													file.thumbUrl ||
													file.url ||
													URL.createObjectURL(file.originFileObj)
												}
												alt={file.name}
												style={{
													width: 40,
													height: 40,
													objectFit: "cover",
													marginRight: 8,
													borderRadius: 4,
												}}
											/>
											<span style={{ color: "#666" }}>{file.name}</span>
										</div>
										<Form.Item
											name={`caption_${file.uid}`}
											initialValue={file.caption}
											noStyle
										>
											<Input.TextArea
												maxLength={500}
												autoSize={{ minRows: 2, maxRows: 4 }}
												placeholder="Digite a legenda para esta imagem"
												style={{ width: "100%" }}
											/>
										</Form.Item>
									</div>
								))}
							</div>
						)}
					</Form.Item>

					<Form.Item name="order" label="Ordem">
						<InputNumber min={0} style={{ width: "100%" }} />
					</Form.Item>

					<Form.Item name="is_active" label="Ativo" valuePropName="checked">
						<Switch />
					</Form.Item>
				</Form>
			</UIDrawerForm>
		);
	}
}

export default Edit;
