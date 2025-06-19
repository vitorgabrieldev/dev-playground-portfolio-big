import React, { Component } from "react";
import { connect } from "react-redux";
import * as PropTypes from "prop-types";
import { Col, Form, Modal, Row, Switch, Select } from "antd";

import moment from "moment";

import { faqService } from "./../../redux/services";

import {
	UIDrawerForm,
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

		faqService.show({uuid})
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
					<Form.Item label="Texto da pergunta">
						{item.name}
					</Form.Item>
					<Form.Item label="Texto da resposta">
						<div className="show-break-lines">{item.text}</div>
					</Form.Item>
					<Form.Item label="Ordem">
						{item.order}
					</Form.Item>
					<Form.Item label="Ativo">
						<Switch disabled checked={item.is_active} />
					</Form.Item>
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
