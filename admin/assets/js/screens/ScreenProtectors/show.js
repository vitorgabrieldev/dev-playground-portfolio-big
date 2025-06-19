import React, { Component } from "react";
import { connect } from "react-redux";
import * as PropTypes from "prop-types";
import { Form, Modal, Switch, Row, Col } from "antd";

import moment from "moment";

import { ScreenProtectorsService } from "./../../redux/services";

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

		ScreenProtectorsService.show({uuid})
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
		if (data.file && this.upload) {
			this.upload.setFiles([
				{
					uuid: data.uuid,
					url : data.file,
					type: 'image/jpeg',
				}
			]);
		}

		if (data.capa && this.uploadCapa) {
			this.uploadCapa.setFiles([
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

					<UIUpload
						ref={el => this.upload = el}
						label="Imagem de capa"
						disabled
					/>

					<UIUpload
						ref={el => this.uploadCapa = el}
						label="Arquivo do protetor de tela"
						disabled
					/>

					<Form.Item label="Ordem">
						{item.order}
					</Form.Item>

					<Form.Item label="Ativo">
						<Switch disabled checked={item.is_active} />
					</Form.Item>

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