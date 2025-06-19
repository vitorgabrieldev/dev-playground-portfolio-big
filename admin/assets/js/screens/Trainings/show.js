import React, { Component } from "react";
import { connect } from "react-redux";
import * as PropTypes from "prop-types";
import { Form, Modal, Switch, Row, Col } from "antd";

import moment from "moment";

import { TrainingsService } from "./../../redux/services";

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

	componentDidUpdate(prevProps, prevState) {
		const { item, hasSetUploadFile } = this.state;
	  
		if (item.capa && !hasSetUploadFile && this.upload) {
		  this.upload.setFiles([
			{
			  uuid: item.uuid,
			  url: item.capa,
			  type: 'image/jpeg',
			},
		  ]);
	  
		  this.setState({ hasSetUploadFile: true });
		}
	}

	onOpen = (uuid) => {
		this.setState({
			...this.stateClean,
		});

		TrainingsService.show({uuid})
		.then((response) => {
			this.setState({
				isLoading: false,
				item     : response.data.data,
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

	onClose = () => {
		// Callback
		this.props.onClose();
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

					<Form.Item label="Categoria">
						{item?.categoria?.name}
					</Form.Item>

					<Form.Item label="Título">
						{item.name}
					</Form.Item>

					<UIUpload
						ref={el => this.upload = el}
						label="Imagem de capa"
						disabled
					/>

					<Form.Item label="Vídeo">
						{item.video}
					</Form.Item>

					<Form.Item label="Duração">
						{item.duracao}
					</Form.Item>

					<Form.Item label="Descrição">
						<span dangerouslySetInnerHTML={{ __html: item.descricao }} />
					</Form.Item>

					<Form.Item label="Ordem">
						{item.order}
					</Form.Item>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item label="Destaque">
								<Switch disabled checked={item.destaque === 1} />
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item label="Ativo">
								<Switch disabled checked={item.is_active === 1} />
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