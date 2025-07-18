import React, { Component, Fragment } from "react";
import { connect } from "react-redux";
import { Button, Dropdown, Menu, Modal } from "antd";
import QueueAnim from "rc-queue-anim";

import moment from "moment";

import { generalActions } from "./../../redux/actions";

import { roleAndPermissionService } from "./../../redux/services";

import {
	UIPageListing,
} from "./../../components";

import ModalCreate from "./create";
import ModalEdit from "./edit";
import ModalShow from "./show";

const config = {
	title            : "Papéis e permissões",
	permissionPrefix : "roles",
	list             : "roles",
	searchPlaceholder: "Buscar por id, nome ou descrição",
	orders           : [
		{
			label  : "Mais recentes",
			field  : "id",
			sort   : "desc",
			default: true,
		},
		{
			label: "Mais antigos",
			field: "id",
			sort : "asc",
		},
		{
			label: "Nome A|Z",
			field: "name",
			sort : "asc",
		},
		{
			label: "Nome Z|A",
			field: "name",
			sort : "desc",
		},
	],
};

class Index extends Component {
	constructor(props) {
		super(props);

		const defaultOrder = config.orders.find(o => o.default);

		this.state = {
			isLoading   : false,
			listType    : "list",
			data        : [],
			pagination  : {
				current : 1,
				pageSize: 20,
				total   : 0,
			},
			orderByLabel: defaultOrder.label,
			orderByField: defaultOrder.field,
			orderBySort : defaultOrder.sort,
			search      : "",
			// Actions
			createModalVisible : false,
			editModalVisible   : false,
			showModalVisible   : false,
			exportModalVisible : false,
			filtersModalVisible: false,
			isExporting        : false,
			// Filters
			totalFilters: 0,
			filters     : {
				created_at: null,
			},
		};
	}

	static getDerivedStateFromProps(props, state) {
		if( props.listType && state.listType !== props.listType )
		{
			return {
				listType: props.listType
			};
		}

		return null;
	}

	componentDidMount() {
		// Fecth all
		this.fetchGetAll(true);
	}

	menuItem = (item) => (
		<Menu className="actions-dropdown-menu">
			{this.props.permissions.includes(config.permissionPrefix + ".show") && <Menu.Item key="show">
				<a onClick={() => this.showOpen(item)}>
					<i className="fal fa-file" />Visualizar
				</a>
			</Menu.Item>}
			{this.props.permissions.includes(config.permissionPrefix + ".edit") && <Menu.Item key="edit">
				<a onClick={() => this.editOpen(item)}>
					<i className="fal fa-pen" />Editar
				</a>
			</Menu.Item>}
			{/*{this.props.permissions.includes(config.permissionPrefix + ".delete") && <Menu.Item key="delete" className="divider btn-delete">*/}
			{/*	<a onClick={() => this.deleteConfirm(item)}>*/}
			{/*		<i className="fal fa-trash" />Excluir*/}
			{/*	</a>*/}
			{/*</Menu.Item>}*/}
		</Menu>
	);

	columns = () => {
		const listTypeCard = this.state.listType === "card";

		return [
			{
				title    : "ID",
				className: "id",
				visible  : !listTypeCard,
				render   : (item) => <span title={item.uuid}>{item.uuid}</span>,
			},
			{
				title : "Nome",
				render: (item) => listTypeCard ? <h3>{item.name}</h3> : item.name,
			},
			{
				title : "Descrição",
				render: (item) => listTypeCard ? <p>{item.description}</p> : item.description,
			},
			{
				title    : "Permissões",
				width    : 120,
				className: "text-center card-block card-block-width-2",
				render   : (item) => {
					if( listTypeCard )
					{
						return (
							<Fragment>
								<span className="value">{item.permissions_count}</span>
								<label>Permissões</label>
							</Fragment>
						)
					}

					return item.permissions_count;
				},
			},
			{
				title    : "Usuários",
				width    : 120,
				className: "text-center card-block card-block-width-2",
				render   : (item) => {
					if( listTypeCard )
					{
						return (
							<Fragment>
								<span className="value">{item.users_count}</span>
								<label>Usuários</label>
							</Fragment>
						)
					}

					return item.users_count;
				},
			},
			{
				title    : "Criação",
				className: "datetime card-block-width-2",
				render   : (item) => {
					if( listTypeCard )
					{
						return (
							<Fragment>
								<i className="fal fa-plus-circle" style={{marginRight: 5}} />{moment(item.created_at).format("DD/MM/YYYY HH:mm")}
							</Fragment>
						);
					}

					return moment(item.created_at).format("DD/MM/YYYY HH:mm");
				},
			},
			{
				title    : "Últ. modificação",
				className: "datetime card-block-width-2",
				render   : (item) => {
					if( listTypeCard )
					{
						return (
							<Fragment>
								<i className="fal fa-pen" style={{marginRight: 5}} />{moment(item.updated_at).format("DD/MM/YYYY HH:mm")}
							</Fragment>
						);
					}

					return moment(item.updated_at).format("DD/MM/YYYY HH:mm");
				},
			},
			{
				title    : "Ações",
				className: "actions no-ellipsis",
				visible  : this.props.permissions.includes(config.permissionPrefix + ".show") || this.props.permissions.includes(config.permissionPrefix + ".edit") || this.props.permissions.includes(config.permissionPrefix + ".delete"),
				render   : (item) => (
					<Dropdown overlay={this.menuItem(item)} className="actions-dropdown" placement="bottomRight" trigger={["click"]}>
						<Button icon={<i className="fal fa-ellipsis-v" />} />
					</Dropdown>
				),
			},
		];
	};

	fetchGetAll = (init = false, exportItems = false) => {
		const {pagination, orderByField, orderBySort, search, filters} = this.state;

		if( exportItems )
		{
			this.setState({
				isExporting: true,
			});
		}
		else
		{
			this.setState({
				isLoading: true,
			});
		}

		const data = {
			orderBy: `${orderByField}:${orderBySort}`,
			search : search,
		};

		if( exportItems )
		{
			data.exportItems = true;
		}
		else
		{
			data.page  = init ? 1 : pagination.current;
			data.limit = pagination.pageSize;
		}

		if( filters.created_at )
		{
			data.created_at = [
				filters.created_at[0].clone().startOf("day").format("YYYY-MM-DDTHH:mm:ssZ"),
				filters.created_at[1].clone().endOf("day").format("YYYY-MM-DDTHH:mm:ssZ")
			];
		}

		roleAndPermissionService.getAll(data)
		.then((response) => {
			if( exportItems )
			{
				this.setState({
					isExporting: false,
				});

				window.open(response.data.file_url, "_blank");
			}
			else
			{
				this.setState(state => ({
					isLoading : false,
					data      : response.data.data,
					pagination: {
						...state.pagination,
						current: response.data.meta.current_page,
						total  : response.data.meta.total,
					},
				}));
			}
		})
		.catch((data) => {
			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
			});
		});
	};

	onListTypeChange = (type) => {
		this.props.onChangeListType(type);
	};

	onPaginationChange = (page) => {
		this.setState(state => ({
			pagination: {
				...state.pagination,
				current: page,
			},
		}), () => {
			this.fetchGetAll();
		});
	};

	onOrderChange = (value) => {
		const defaultOrder = config.orders.find(o => `${o.field}:${o.sort}` === value);

		if( !defaultOrder ) return null;

		this.setState(state => ({
			orderByLabel: defaultOrder.label,
			orderByField: defaultOrder.field,
			orderBySort : defaultOrder.sort,
		}), () => {
			this.fetchGetAll(true);
		});
	};

	onSearch = (value) => {
		this.setState({
			search: value,
		}, () => {
			this.fetchGetAll(true);
		});
	};

	onSearchChange = (e) => {
		// If it does not have type then it's cleaning
		if( !e.hasOwnProperty("type") )
		{
			const {search} = this.state;

			this.setState({
				search: e.target.value,
			}, () => {
				if( search )
				{
					this.fetchGetAll(true);
				}
			});
		}
	};

	/**
	 * Create
	 */
	createOpen = () => {
		this.setState({createModalVisible: true});

		// On open screen
		this.createScreen.onOpen();
	};

	createOnClose = () => {
		this.setState({createModalVisible: false});
	};

	createOnComplete = () => {
		this.setState({createModalVisible: false});

		// Fecth all
		this.fetchGetAll(true);
	};

	/**
	 * Edit
	 *
	 * @param uuid
	 * @param is_system
	 */
	editOpen = ({uuid, is_system}) => {
		if( is_system )
		{
			Modal.warning({
				title  : "Ação indisponível",
				content: "Este item é bloqueado pelo sistema e não pode ser editado.",
			});

			return false;
		}

		this.setState({editModalVisible: true});

		// On open screen
		this.editScreen.onOpen(uuid);
	};

	editOnClose = () => {
		this.setState({editModalVisible: false});
	};

	editOnComplete = () => {
		this.setState({editModalVisible: false});

		// Fecth all
		this.fetchGetAll();
	};

	/**
	 * Show
	 *
	 * @param uuid
	 */
	showOpen = ({uuid}) => {
		this.setState({showModalVisible: true});

		// On open screen
		this.showScreen.onOpen(uuid);
	};

	showOnClose = () => {
		this.setState({showModalVisible: false});
	};

	/**
	 * Delete
	 *
	 * @param uuid
	 * @param is_system
	 */
	deleteConfirm = ({uuid, is_system}) => {
		if( is_system )
		{
			Modal.warning({
				title  : "Ação indisponível",
				content: "Este item é bloqueado pelo sistema e não pode ser deletado.",
			});

			return false;
		}

		Modal.confirm({
			title          : "Confirmar exclusão!",
			content        : "Tem certeza de que deseja excluir este registro?",
			okText         : "Excluir",
			autoFocusButton: null,
			onOk           : () => {
				return this.deleteConfirmed(uuid);
			}
		});
	};

	deleteConfirmed = (uuid) => {
		return roleAndPermissionService.destroy({uuid})
		.then((response) => {
			// Fecth all
			this.fetchGetAll();
		})
		.catch((data) => {
			Modal.error({
				title  : "Ocorreu um erro!",
				content: String(data),
			});
		});
	};

	/**
	 * Filter
	 */
	filtersOpen = () => {
		this.setState({filtersModalVisible: true});

		// On open screen
		this.filtersScreen.onOpen({...this.state.filters});
	};

	filtersOnClose = () => {
		this.setState({filtersModalVisible: false});
	};

	filtersOnComplete = (filters) => {
		this.setState({filtersModalVisible: false});

		this.setState({
			totalFilters: Object.keys(filters).filter(key => filters.hasOwnProperty(key) && filters[key] !== null).length,
			filters     : filters,
		}, () => {
			// Fecth all
			this.fetchGetAll(true);
		});
	};

	render() {
		return (
			<QueueAnim className="site-content-inner">
				<div className="page-content" key="1">
					<h1 className="page-title">{config.title}</h1>
					<UIPageListing
						onSearch={this.onSearch}
						onSearchChange={this.onSearchChange}
						onPaginationChange={this.onPaginationChange}
						onOrderChange={this.onOrderChange}
						onListTypeChange={this.onListTypeChange}
						onFiltersClick={this.filtersOpen}
						isLoading={this.state.isLoading}
						listType={this.state.listType}
						orderByField={this.state.orderByField}
						orderBySort={this.state.orderBySort}
						enableSearch={false}
						search={this.state.search}
						searchPlaceholder={config.searchPlaceholder}
						data={this.state.data}
						showListTypeChange={false}
						pagination={this.state.pagination}
						columns={this.columns()}
						showFilters={false}
						totalFilters={this.state.totalFilters}
						buttons={[
							{
								visible: this.props.permissions.includes(config.permissionPrefix + ".create"),
								onClick: this.createOpen,
								title  : "Cadastrar",
								icon   : <i className="far fa-plus" />,
							},
							{
								visible: this.props.permissions.includes(config.permissionPrefix + ".export"),
								onClick: () => this.fetchGetAll(true, true),
								title  : this.state.isExporting ? "Exportando" : "Exportar",
								icon   : <i className="fal fa-file-export" />,
								loading: this.state.isExporting,
							},
						]}
					/>
				</div>
				<ModalCreate
					ref={el => this.createScreen = el}
					visible={this.state.createModalVisible}
					onComplete={this.createOnComplete}
					onClose={this.createOnClose}
				/>
				<ModalEdit
					ref={el => this.editScreen = el}
					visible={this.state.editModalVisible}
					onComplete={this.editOnComplete}
					onClose={this.editOnClose}
				/>
				<ModalShow
					ref={el => this.showScreen = el}
					visible={this.state.showModalVisible}
					onClose={this.showOnClose}
				/>
			</QueueAnim>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		permissions: state.auth.userData.permissions,
		listType   : state.general.listType[config.list],
	};
};

const mapDispatchToProps = (dispatch, ownProps) => {
	return {
		onChangeListType: (type) => {
			dispatch(generalActions.changeListType(config.list, type));
		}
	}
};

export default connect(mapStateToProps, mapDispatchToProps)(Index);
