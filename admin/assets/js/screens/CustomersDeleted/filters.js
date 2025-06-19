import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Button, DatePicker, Form, Modal, Radio, Input, Select, Spin } from "antd";
import { PreferencesService, AccessProfilesService } from "./../../redux/services";
import axios from "axios";
import moment from "moment";

class Filters extends Component {
	static propTypes = {
		visible   : PropTypes.bool.isRequired,
		onComplete: PropTypes.func.isRequired,
		onClose   : PropTypes.func.isRequired,
	};

	constructor(props) {
		super(props);

		this.filtersClean = {
			is_active  : null,
			created_at : null,
			search_cpf : null,
			perfil_id : null,
			preferencias: [],
		};

		this.state = {
			filters: {
				...this.filtersClean,
			},
			preferenciaIsLoading : false,
			preferencias : [],
			perfisacessoIsLoading : false,
			perfisacesso : [],
		};
	}

	onOpen = (filters) => {
		const perfilSelecionado = this.state.perfisacesso.find(p => p.uuid === filters.perfil_id);
		const perfil_id = perfilSelecionado ? { value: perfilSelecionado.uuid, label: perfilSelecionado.name } : null;
	
		const preferenciasSelecionadas = Array.isArray(filters.preferencias)
			? filters.preferencias.map(uuid => {
				const pref = this.state.preferencias.find(p => p.uuid === uuid);
				return pref ? { value: pref.uuid, label: pref.name } : { value: uuid, label: uuid };
			})
			: [];
	
		this.setState({
			filters: {
				...filters,
				perfil_id,
				preferencias: preferenciasSelecionadas,
			}
		});
	};

	onChangePerfil = (e) => {
		this.setFilter("perfil_id", e);
	};
	
	onChangePreferencias = (values) => {
		this.setFilter("preferencias", values);
	};

	cleanFilters = () => {
		this.setState({
			filters: this.filtersClean,
		}, () => {
			// Callback
			this.props.onComplete({...this.state.filters});
		});
	};

	onClose = () => {
		// Callback
		this.props.onClose();
	};

	filtersOnConfirm = () => {
		// Callback
		this.props.onComplete({...this.state.filters});
	};

	setFilter = (name, value) => {
		this.setState(state => ({
			filters: {
				...state.filters,
				[name]: value,
			}
		}));
	};

	formatCPF = (value) => {
		const numbers = value.replace(/\D/g, '');
		if (numbers.length <= 3) {
			return numbers;
		} else if (numbers.length <= 6) {
			return `${numbers.slice(0, 3)}.${numbers.slice(3)}`;
		} else if (numbers.length <= 9) {
			return `${numbers.slice(0, 3)}.${numbers.slice(3, 6)}.${numbers.slice(6)}`;
		} else {
			return `${numbers.slice(0, 3)}.${numbers.slice(3, 6)}.${numbers.slice(6, 9)}-${numbers.slice(9, 11)}`;
		}
	};

	fetchPreferencias = (value) => {
		if (this._axiosCancelPreferenciasToken) {
			this._axiosCancelPreferenciasToken.cancel("Only one request allowed at a time.");
		}

		this._axiosCancelPreferenciasToken = axios.CancelToken.source();

		this.setState({
			preferenciaIsLoading: true,
		});

		PreferencesService.getAutocomplete({
			search: value,
			cancelToken: this._axiosCancelPreferenciasToken.token,
		})
			.then((response) => {
				let preferencias = [];

				response.data.data.forEach(categoria => {
					preferencias.push({
						name: categoria.name,
						uuid: categoria.uuid
					});
				});

				this.setState({
					preferenciaIsLoading: false,
					preferencias,
				});
			})
			.catch((data) => {
				if (data.error_type === API_ERRO_TYPE_CANCEL) return null;

				this.setState({
					preferenciaIsLoading: false,
				});

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	fetchPerfisAcesso = (value) => {
		if (this._axiosCancelPerfisdeacessoToken) {
			this._axiosCancelPerfisdeacessoToken.cancel("Only one request allowed at a time.");
		}

		this._axiosCancelPerfisdeacessoToken = axios.CancelToken.source();

		this.setState({
			perfisacessoIsLoading: true,
		});

		AccessProfilesService.getAutocomplete({
			search: value,
			cancelToken: this._axiosCancelPerfisdeacessoToken.token,
		})
			.then((response) => {
				this.setState({
					perfisacessoIsLoading: false,
					perfisacesso: response.data.data,
				});
			})
			.catch((data) => {
				if (data.error_type === API_ERRO_TYPE_CANCEL) return null;

				this.setState({
					perfisacessoIsLoading: false,
				});

				Modal.error({
					title: "Ocorreu um erro!",
					content: String(data),
				});
			});
	};

	render() {
		const {visible} = this.props;

		const {filters, preferenciaIsLoading, preferencias, perfisacesso, perfisacessoIsLoading} = this.state;

		return (
			<Modal
				visible={visible}
				title="Filtrar"
				centered={true}
				destroyOnClose={true}
				maskClosable={true}
				width={900}
				okText="Aplicar"
				onCancel={this.onClose}
				onOk={this.filtersOnConfirm}
				className="modal-filters"
				footer={[
					<Button key="back" type="link" onClick={this.cleanFilters}>Excluir filtros</Button>,
					<Button key="submit" type="primary" onClick={this.filtersOnConfirm}>Aplicar</Button>,
				]}>
	
				<div className="filter-group">
					<div className="filter-group-title">
						<h3>CPF</h3>
					</div>
					<div className="filter-group-filters" style={{paddingBottom: 5}}>
						<Form.Item>
							<Input
								placeholder="Digite o CPF"
								value={filters.search_cpf}
								onChange={(e) => {
									const maskedValue = this.formatCPF(e.target.value);
									this.setFilter("search_cpf", maskedValue);
								}}
								maxLength={14}
							/>
						</Form.Item>
					</div>
				</div>

				<div className="filter-group">
					<div className="filter-group-title">
						<h3>Perfil de acesso</h3>
					</div>
					<div className="filter-group-filters" style={{paddingBottom: 0}}>
						<Form.Item name="store_id">
							<Select
								filterOption={false}
								allowClear
								onChange={this.onChangePerfil}
								showSearch
								labelInValue={true}
								value={filters.perfil_id}
								notFoundContent={perfisacessoIsLoading ? <Spin indicator={<i className="fad fa-spinner-third fa-spin" />} /> : null}
								onSearch={this.fetchPerfisAcesso}
								options={perfisacesso.map((item) => ({
									value: item.uuid,
									label: item.name
								}))}
							/>
						</Form.Item>
					</div>
				</div>

				<div className="filter-group">
					<div className="filter-group-title" style={{paddingTop: 0}}>
						<h3>Preferências</h3>
					</div>
					<div className="filter-group-filters" style={{paddingBottom: 5}}>
						<Form.Item name="preferencias">
							<Select
								mode="multiple"
								filterOption={false}
								allowClear
								value={filters.preferencias}
								onChange={this.onChangePreferencias}
								showSearch
								labelInValue={true}
								notFoundContent={
									preferenciaIsLoading ? (
										<Spin indicator={<i className="fad fa-spinner-third fa-spin" />} />
									) : null
								}
								onSearch={this.fetchPreferencias}
								options={preferencias.map((item) => ({
									value: item.uuid,
									label: item.name,
								}))}
							/>
						</Form.Item>
					</div>
				</div>

				<div className="filter-group">
					<div className="filter-group-title">
						<h3>Período do cadastro</h3>
					</div>
					<div className="filter-group-filters" style={{paddingBottom: 0}}>
						<Form.Item>
							<DatePicker.RangePicker
								format="DD/MM/YYYY"
								value={filters.created_at}
								onChange={(date, dateString) => this.setFilter("created_at", date ?? null)}
								disabledDate={(currentDate) => currentDate.isAfter(moment(), "day")}
							/>
						</Form.Item>
					</div>
				</div>

				<div className="filter-group">
					<div className="filter-group-title" style={{paddingTop: 0}}>
						<h3>Status</h3>
					</div>
					<div className="filter-group-filters" style={{paddingBottom: 5}}>
						<div className="filter-group-radios">
							<div className="filter-group-radio">
								<Radio onChange={(e) => this.setFilter("is_active", null)} checked={filters.is_active === null}>Todos</Radio>
							</div>
							<div className="filter-group-radio">
								<Radio onChange={(e) => this.setFilter("is_active", 1)} checked={filters.is_active === 1}>Ativo</Radio>
							</div>
							<div className="filter-group-radio">
								<Radio onChange={(e) => this.setFilter("is_active", 0)} checked={filters.is_active === 0}>Inativo</Radio>
							</div>
						</div>
					</div>
				</div>
			</Modal>
		)
	}
}

export default Filters;
