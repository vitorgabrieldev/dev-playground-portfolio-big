import React, { Component, Fragment } from "react";
import { connect } from "react-redux";
import { Card, Col, Modal, Row, Spin } from "antd";
import QueueAnim from "rc-queue-anim";

import moment from "moment";

import { dashboardService } from "./../../redux/services";

class Home extends Component {
	constructor(props) {
		super(props);

		this.state = {
			isLoading                  : true,
			customer_ativo_total       : 0,
			customer_deleted_total     : 0,
		};
	}

	componentDidMount() {
		// Fecth all
		this.fetchGetAll();
	};

	fetchGetAll = () => {
		this.setState({
			isLoading: true,
		});

		dashboardService.getAll()
		.then((response) => {
			this.setState(state => ({
				isLoading      : false,
				customer_ativo_total : response.data.data.customer_ativo_total,
				customer_deleted_total : response.data.data.customer_deleted_total,
				ecwid_products_total : response.data.data.ecwid_products_total,
			}));
		})
		.catch((data) => {
			this.setState({
				isLoading: false,
			});

			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
			});
		});
	};

	greeting = () => {
		const hour = moment().hour();
		let day    = "Bom dia";

		if( hour >= 19 )
		{
			day = "Boa noite";
		}
		else if( hour >= 12 )
		{
			day = "Boa tarde";
		}

		return `Olá ${this.props.user.name}, ${day}!`;
	};

	render() {
		const {isLoading} = this.state;

		return (
			<QueueAnim className="site-content-inner page-home">
				<div className="page-content" key="1">
					<h1 className="page-title">{this.greeting()}</h1>
					{isLoading ? (
						<div className="text-center">
							<Spin indicator={<i className="fad fa-spinner-third fa-spin fa-3x" />} />
						</div>
					) : (
						<Fragment>
							<div className="cards">
								<Row gutter={16}>
									<Col xs={24} sm={8} lg={6} xxl={4}>
										<Card data-has-link={true} onClick={() => this.props.permissions.includes("customers.list") && this.props.history.push('/list/customers?is_active=1')}>
											<h3>Usuários ativos</h3>
											<div className="value">{this.state.customer_ativo_total}</div>
											<i className="fad fa-users" />
										</Card>
									</Col>
									<Col xs={24} sm={8} lg={6} xxl={4}>
										<Card data-has-link={true} onClick={() => this.props.permissions.includes("customers.list") && this.props.history.push('/list-deleted/customers-deleted')}>
											<h3>Usuários removidos</h3>
											<div className="value">{this.state.customer_deleted_total}</div>
											<i className="fad fa-user-slash" />
										</Card>
									</Col>
									<Col xs={24} sm={8} lg={6} xxl={4}>
										<Card>
											<h3>Total de equipamentos</h3>
											<div className="value">{this.state.ecwid_products_total}</div>
											<i className="fad fa-tools" />
										</Card>
									</Col>
								</Row>
							</div>
						</Fragment>
					)}
				</div>
			</QueueAnim>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		user       : state.auth.userData,
		permissions: state.auth.userData.permissions,
	};
};

export default connect(mapStateToProps)(Home);
