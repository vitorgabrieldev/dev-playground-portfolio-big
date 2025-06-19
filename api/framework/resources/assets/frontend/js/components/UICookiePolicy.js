import React, { Component, Fragment } from "react";
import axios from "axios";
import { connect } from "react-redux";
import { withRouter } from "react-router-dom";
import { Button, Modal } from "antd";

import UIError from "./UIError";
import UISkeleton from "./UISkeleton";

import { API_ERRO_TYPE_CANCEL } from "./../config/general";

import { generalActions } from "./../redux/actions";

import { institutionalService } from "./../redux/services";

class UICookiePolicy extends Component {
	constructor(props) {
		super(props);

		this.state = {
			visible  : false,
			isLoading: true,
			hasError : false,
			error    : '',
			item     : null,
			hide     : !!this.props.cookiePolicy,
		};

		this._cancelToken = null;
	}

	componentWillUnmount() {
		this._cancelToken && this._cancelToken.cancel("Landing Component got unmounted");
	}

	fetchItem = () => {
		this.setState({
			isLoading: true,
			hasError : false,
			error    : '',
		});

		this._cancelToken = axios.CancelToken.source();

		institutionalService.cookiePolicy(this._cancelToken.token)
		.then((response) => {
			this.setState({
				isLoading: false,
				item     : response.data.data,
			});
		})
		.catch((data) => {
			if( data?.error_type === API_ERRO_TYPE_CANCEL ) return null;

			this.setState({
				isLoading: false,
				hasError : true,
				error    : String(data),
			});
		});
	}

	showModal = () => {
		this.setState({
			visible: true,
		});

		if( !this.state.item )
		{
			this.fetchItem();
		}
	}

	confirm = () => {
		this.setState({
			visible: false,
		});

		// Confirm
		this.props.doConfirmCookiePolicy();

		const el = document.getElementsByClassName('component-cookie-policy');

		if( el.length )
		{
			el[0].classList.add('hide');
		}

		setTimeout(() => {
			this.setState({
				hide: true,
			});
		}, 1000);
	}

	render() {
		const {location} = this.props;

		// Disable
		if( location.pathname.startsWith('/app/') )
		{
			return null;
		}

		if( this.state.hide )
		{
			return null;
		}

		return (
			<Fragment>
				<div className="component-cookie-policy">
					<div className="inner">
						<p>Usamos cookies para personalizar e melhorar sua experiência em nosso site e aprimorar a oferta de anúncios para você. Visite nossa <a onClick={this.showModal}>Política de Cookies</a> para saber mais. Ao clicar em "aceitar" você concorda com o uso que fazemos dos cookies.</p>
						<div className="text-center text-right-md">
							<Button size="small" onClick={this.confirm}>Aceitar</Button>
						</div>
					</div>
				</div>
				<Modal
					visible={this.state.visible}
					wrapClassName="modal-text modal-text-scroll-inside"
					footer={null}
					centered
					destroyOnClose={true}
					onCancel={() => this.setState({visible: false})}
					autoFocusButton={false}
					focusTriggerAfterClose={false}>
					<h2 className="title">Política de privacidade</h2>
					{this.state.isLoading ? (
						<UISkeleton type="longtext" />
					) : (
						<Fragment>
							{this.state.hasError ? (
								<UIError text={this.state.error} onPressOk={this.fetchItem} />
							) : (
								<div dangerouslySetInnerHTML={{__html: this.state.item?.text ?? ''}} />
							)}
						</Fragment>
					)}
				</Modal>
			</Fragment>
		);
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		cookiePolicy: state.general.cookiePolicy,
	};
};

const mapDispatchToProps = (dispatch, ownProps) => {
	return {
		doConfirmCookiePolicy: () => {
			dispatch(generalActions.cookiePolicy());
		},
	}
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(UICookiePolicy));
