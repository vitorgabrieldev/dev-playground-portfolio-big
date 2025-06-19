import React, { Component } from "react";
import { connect } from "react-redux";
import * as PropTypes from "prop-types";
import { Form, Modal, Switch, Row, Col } from "antd";

import moment from "moment";

import { NotificationsService } from "./../../redux/services";

import {
	UIDrawerForm,
	UIUpload,
} from "./../../components";

class Show extends Component {
	static propTypes = {
		visible : PropTypes.bool.isRequired,
		onClose : PropTypes.func.isRequired,
		external: PropTypes.bool,
	};

	constructor(props) {
		super(props);

		this.stateClean = {
			isLoading: true,
			uuid     : 0,
			item     : {},
		};

		this.state = {
			...this.stateClean,
		};
	}

	onOpen = (uuid) => {
		this.setState({
			...this.stateClean,
		});

		NotificationsService.show({uuid})
		.then((response) => {
			this.setState({
				isLoading: false,
				item     : response.data.data,
			});
			this.fillForm(response.data.data);
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

	onClose = () => {
		// Callback
		this.props.onClose();
	};

	fillForm = (data) => {
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

	render() {
		const {visible} = this.props;
		const {isLoading, item} = this.state;

		return (
			<UIDrawerForm
				visible={visible}
				width={500}
				onClose={this.onClose}
				isLoading={isLoading}
				showBtnSave={false}
				title={`Visualizar registro`}>
				<Form layout="vertical">

					<Form.Item label="Título">
						{item.name}
					</Form.Item>

					<Form.Item label="Descrição">
						{item.descricao}
					</Form.Item>

					<UIUpload
						ref={el => this.upload = el}
						label="Imagens"
						labelError="imagens"
						acceptedFiles={['png', 'jpg', 'jpeg']}
						maxFiles={10}
						minFiles={1}
						disabled
					/>

					<Form.Item label="Vídeo">
						<a href={item.video} target="_blank" rel="noopener noreferrer">
							{item.video}
						</a>
					</Form.Item>

					<Form.Item label="Texto">
						<div dangerouslySetInnerHTML={{ __html: item.text }} />
					</Form.Item>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item label="Enviar push?">
								<Switch disabled checked={item.send_push} />
							</Form.Item>
						</Col>
					</Row>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item label="Data e hora do cadastro">
								{moment(item.created_at).calendar()}
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item label="Última modificação">
								{moment(item.updated_at).calendar()}
							</Form.Item>
						</Col>
					</Row>
				</Form>
			</UIDrawerForm>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		permissions: state.auth.userData.permissions,
	};
};

export default connect(mapStateToProps, null, null, {forwardRef: true})(Show);