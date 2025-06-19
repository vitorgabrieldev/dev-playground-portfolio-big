import React, { Component } from "react";
import axios from "axios";
import { Button, Col, Form, Input, message, Modal, Row, Spin } from "antd";
import QueueAnim from "rc-queue-anim";
import _maxBy from "lodash/maxBy";

import { API_ERRO_TYPE_CANCEL } from "./../../config/general";

import { settingService } from "./../../redux/services";

import {
	UIUpload,
} from "./../../components";

class Index extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isLoading      : true,
			isSending      : false,
		};

		this._cancelToken      = null;
		this._axiosCancelToken = null;
	}

	componentDidMount() {
		this._cancelToken = axios.CancelToken.source();

		settingService.getGeneral(this._cancelToken.token)
		.then((response) => {
			this.setState({
				isLoading: false,
			}, () => {
				// Fill form
				this.fillForm(response.data.data);
			});
		})
		.catch((data) => {
			if( data?.error_type === API_ERRO_TYPE_CANCEL ) return null;

			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
			});
		});
	}

	componentWillUnmount() {
		this._cancelToken && this._cancelToken.cancel("Landing Component got unmounted");
	}

	fillForm = (data) => {
		const dataForm = {
			wpp_geral  	: data.wpp_geral,
			wpp_equip   : data.wpp_equip,
		};

		this.form && this.form.setFieldsValue(dataForm);

		// Set PDF file if exists
		if (data.catalogo) {
			this.upload.setFiles([
				{
					uuid: data.uuid,
					url: data.catalogo,
					type: 'application/pdf'
				}
			]);
		}
	};

	onFinish = (values) => {
		this.setState({
			isSending: true,
		});

		const data = { ...values };
	
		// File - Catalogo
		const uploadFiles = this.upload.getFiles();

		console.log(uploadFiles);

		const files = uploadFiles.files;

		if (files.length === 0) {
			data.delete_catalogo = true;
		} else {
			const file = files[0];

			if (file.status !== "done") {
				data.catalogo = file;
			}
		}
		
		settingService.updateGeneral(data)
		.then((response) => {
			this.setState({
				isSending: false,
			});

			// Success message
			message.success("Configuração atualizada.");
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
		const {isLoading, isSending} = this.state;

		return (
			<QueueAnim className="site-content-inner page-settings">
				<div className="page-content" key="1">
					<h1 className="page-title">Gerais</h1>
					<Form
						ref={el => this.form = el}
						layout="vertical"
						scrollToFirstError
						onFinish={this.onFinish}>
						{isLoading ? (
							<div className="text-center" style={{padding: 20}}>
								<Spin indicator={<i className="fad fa-spinner-third fa-spin fa-3x" />} />
							</div>
						) : (
							<Row gutter={24}>
								<Col xs={24} sm={14}>
									<Form.Item name="wpp_geral" label="WhatsApp geral">
										<Input 
											placeholder="(00) 00000-0000"
											maxLength={15}
											onChange={(e) => {
												let value = e.target.value.replace(/\D/g, '');
												if (value.length > 11) {
													value = value.slice(0, 11);
												}
												if (value.length > 0) {
													value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
													value = value.replace(/(\d)(\d{4})$/, '$1-$2');
												}
												this.form.setFieldValue('wpp_geral', value);
											}}
										/>
									</Form.Item>
								</Col>
								<Col xs={24} sm={14}>
									<Form.Item name="wpp_equip" label="WhatsApp de equipamentos">
										<Input 
											placeholder="(00) 00000-0000"
											maxLength={15}
											onChange={(e) => {
												let value = e.target.value.replace(/\D/g, '');
												if (value.length > 11) {
													value = value.slice(0, 11);
												}
												if (value.length > 0) {
													value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
													value = value.replace(/(\d)(\d{4})$/, '$1-$2');
												}
												this.form.setFieldValue('wpp_equip', value);
											}}
										/>
									</Form.Item>
								</Col>

								<Col xs={24} sm={14}>
									<UIUpload
										ref={el => (this.upload = el)}
										label="Catálogo AIZ"
										acceptedFiles={['pdf']}
										maxFileSize={10}
										labelError="arquivo"
									/>
								</Col>

								<Col xs={24} sm={14}>
									<Button style={{marginTop: 40}} type="primary" htmlType="submit" icon={<i className="far fa-check" />} loading={isSending} disabled={isLoading}>
										{isSending ? "Salvando" : "Salvar"}
									</Button>
								</Col>
							</Row>
						)}
					</Form>
				</div>
			</QueueAnim>
		)
	}
}

export default Index;
