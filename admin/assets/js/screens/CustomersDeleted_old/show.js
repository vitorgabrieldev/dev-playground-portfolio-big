import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Row, Col, Modal, Switch, Tabs, List, Avatar, Typography, Card } from "antd";

import moment from "moment";

import { customerDeletedService } from "./../../redux/services";

import {
	UIDrawerForm,
	UIUpload,
} from "./../../components";

const config = {
	externalName: "cliente",
};

class Show extends Component {
	static propTypes = {
		visible : PropTypes.bool.isRequired,
		onClose : PropTypes.func.isRequired,
		external: PropTypes.bool,
	};

	constructor(props) {
		super(props);

		this.state = {
			isLoading: true,
			uuid     : 0,
			item     : {},
		};
	}

	onOpen = (uuid) => {
		this.state = {
			isLoading: true,
			uuid     : 0,
			item     : {},
			previewVisible: false,
			previewImage: '',
		};

		customerDeletedService.show({uuid})
		.then((response) => {
			let item = response.data.data;

			this.setState({
				isLoading: false,
				item     : item,
			}, () => {
				// Upload
				if( item.avatar )
				{
					this.upload.setFiles([
						{
							uuid: item.uuid,
							url : item.avatar,
							type: 'image/jpeg',
						}
					]);
				}
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

	resetFields = () => {
		this.setState({
			item: {},
		});
	};

	onClose = () => {
		// Reset fields
		this.resetFields();

		// Callback
		this.props.onClose();
	};

	render() {
		const {visible}               = this.props;
		const {uuid, isLoading, item} = this.state;

		return (
			<UIDrawerForm
				visible={visible}
				width={500}
				onClose={this.onClose}
				isLoading={isLoading}
				showBtnSave={false}
				title={`Visualizar registro`}>
				<Form layout="vertical">
					<Tabs defaultActiveKey="general">
						<Tabs.TabPane forceRender tab="Infos gerais" key="general">
							<UIUpload
								ref={el => (this.upload = el)}
								label="Imagem"
								disabled
							/>

							<Form.Item label="Nome completo">
								{item.name || 'N/A'}
							</Form.Item>

							<Row gutter={16}>
								<Col xs={24} sm={12}>
									<Form.Item label="CPF">
										{item.document || 'N/A'}
									</Form.Item>
								</Col>
								<Col xs={24} sm={12}>
									<Form.Item label="Telefone">
										{item.phone || '-'}
									</Form.Item>
								</Col>
							</Row>

							<Row gutter={16}>
								<Col xs={24} sm={12}>
									<Form.Item label="E-mail">
										{item.email || 'N/A'}
									</Form.Item>
								</Col>
								<Col xs={24} sm={12}>
									<Form.Item label="Nota">
										{item.nota ?? '-'}
									</Form.Item>
								</Col>
							</Row>

							<Row gutter={16}>
								<Col xs={24} sm={12}>
									<Form.Item label="Aceite dos Termos de uso">
										{item.accepted_term_of_users_at ? moment(item.accepted_term_of_users_at).format("DD/MM/YYYY HH:mm") : 'N/A'}
									</Form.Item>
								</Col>
								<Col xs={24} sm={12}>
									<Form.Item label="Aceite da Política de privacidade">
										{item.accepted_policy_privacy_at ? moment(item.accepted_policy_privacy_at).format("DD/MM/YYYY HH:mm") : 'N/A'}
									</Form.Item>
								</Col>
							</Row>

							<Row gutter={16}>
								<Col xs={24} sm={12}>
									<Form.Item label="Data e hora do cadastro">
										{item.created_at ? moment(item.created_at).format("DD/MM/YYYY HH:mm") : 'N/A'}
									</Form.Item>
								</Col>
								<Col xs={24} sm={12}>
									<Form.Item label="Última alteração">
										{item.updated_at ? moment(item.updated_at).format("DD/MM/YYYY HH:mm") : 'Sem informação'}
									</Form.Item>
								</Col>
							</Row>

							<Row gutter={16}>
								<Col xs={24} sm={12}>
									<Form.Item label="Data e hora da remoção da conta">
										{item.deleted_at ? moment(item.deleted_at).format("DD/MM/YYYY HH:mm") : 'N/A'}
									</Form.Item>
								</Col>
							</Row>

							<Form.Item label="Ativo">
								<Switch disabled checked={item.is_active} />
							</Form.Item>
							
						</Tabs.TabPane>
		
						<Tabs.TabPane forceRender tab="Motocicletas" key="favorites">
							{item.motorcycle && item.motorcycle.length > 0 ? (
								item.motorcycle.map((moto) => (
									<Card key={moto.uuid} style={{ marginBottom: 16 }}>
										{moto.avatar && (
											<img
												src={moto.avatar}
												alt="Imagem da moto"
												onClick={() => this.setState({ previewVisible: true, previewImage: moto.avatar })}
												style={{
													maxWidth: 100,
													marginBottom: 12,
													display: "block",
													cursor: "pointer",
													borderRadius: 8,
													boxShadow: "0 2px 8px rgba(0,0,0,0.15)"
												}}
											/>
										)}

										<Row gutter={16}>
											<Col span={12}>
												<Form.Item label="Marca">
													{moto.marca?.name || 'N/A'}
												</Form.Item>
											</Col>
											<Col span={12}>
												<Form.Item label="Modelo">
													{moto.modelo?.name || 'N/A'}
												</Form.Item>
											</Col>
										</Row>
										<Row gutter={16}>
											<Col span={12}>
												<Form.Item label="Ano de fabricação">
													{moto.ano_fabricacao || 'N/A'}
												</Form.Item>
											</Col>
											<Col span={12}>
												<Form.Item label="Ano do modelo">
													{moto.ano_modelo || 'N/A'}
												</Form.Item>
											</Col>
										</Row>
									</Card>
								))
							) : (
								<Typography.Text type="secondary">Nenhuma motocicleta cadastrada.</Typography.Text>
							)}

							<Modal visible={this.state.previewVisible} footer={null} centered onCancel={() => this.setState({ previewVisible: false })}destroyOnClose>
								<img src={this.state.previewImage} style={{ width: "100%" }} />
							</Modal>
						</Tabs.TabPane>
					</Tabs>
				</Form>
			</UIDrawerForm>
		)
	}
}

export default Show;