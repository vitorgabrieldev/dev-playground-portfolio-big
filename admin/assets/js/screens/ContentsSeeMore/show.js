import React, { Component } from "react";
import { connect } from "react-redux";
import * as PropTypes from "prop-types";
import { Form, Modal, Switch, Row, Col } from "antd";
import moment from "moment";

import { contentsSeeMoreService } from "./../../redux/services";

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

		contentsSeeMoreService.show({uuid})
		.then((response) => {
			const item = response.data.data;
			this.setState({
				isLoading: false,
				item     : item,
			}, () => {
				// Upload
				// if( item.media )
				// {
				// 	let images = [];
				// 	item?.media.forEach(imgs => {
				// 		images.push({
				// 			uuid: imgs.uuid,
				// 			url : imgs.file,
				// 			type: 'image/jpeg',
				// 		});
				// 	});
				// 	this.upload.setFiles(images);
				// }
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

					<Form.Item label="Título">
						{item.name}
					</Form.Item>

					<Form.Item label="Sinopse">
						{item.sinopse}
					</Form.Item>

					<Form.Item label="Imagens">
						{item?.media?.length > 0 ? (
							<div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
								{item.media.map(img => (
									<div key={img.uuid} style={{ display: 'flex', gap: 12 }}>
										<img 
											src={img.file}
											alt="Imagem"
											style={{
												width: 80,
												height: 80,
												objectFit: 'cover',
												borderRadius: 4,
												border: '1px solid #ddd'
											}}
										/>
										<div style={{ flex: 1 }}>
											<div style={{ fontWeight: 500, color: '#333' }}>{img.description || <i>Sem legenda</i>}</div>
										</div>
									</div>
								))}
							</div>
						) : (
							<div>Nenhuma imagem enviada.</div>
						)}
					</Form.Item>

					<Form.Item label="Texto">
						<div dangerouslySetInnerHTML={{ __html: item.text }} />
					</Form.Item>

					<Form.Item label="Data">
						{moment(item.data_inicio).format('DD/MM/YYYY HH:mm')}
					</Form.Item>

					<Form.Item label="Ativo">
						<Switch disabled checked={item.is_active === 1} />
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