import { Menu } from "antd";
import * as PropTypes from "prop-types";
import React, { Component } from "react";
import { connect } from "react-redux";
import { NavLink, withRouter } from "react-router-dom";

const SubMenu = Menu.SubMenu;

class MainNavigation extends Component {
  static propTypes = {
    onClick: PropTypes.func,
  };

  static defaultProps = {
    onClick: () => null,
  };

  state = {
    openKeys: [],
  };

  onOpenChange = (keys) => {
    const latestOpenKey = keys.find(key => this.state.openKeys.indexOf(key) === -1);
    
    if (latestOpenKey) {
      this.setState({ openKeys: [latestOpenKey] });
    } else {
      this.setState({ openKeys: [] });
    }
  };

  render() {
    const { location } = this.props;
    let base = "";
    let selectedKeys = [];
    let paths = location.pathname.split("/").filter(function (el) {
      return el;
    });

    let pathsGroups = "";

    paths.forEach((path, index) => {
      if (path) {
        if (index === 0) {
          base = `/${path}`;
        }

        pathsGroups += `/${path}`;
      }
    });

    selectedKeys.push(location.pathname);
    selectedKeys.push(base);

		return (
			<Menu 
				theme="dark" 
				mode="inline" 
				selectedKeys={selectedKeys}
				openKeys={this.state.openKeys}
				onOpenChange={this.onOpenChange}
				onClick={this.props.onClick}
			>
				<Menu.Item key="/" icon={<i className="fal fa-tachometer-fast" />}>
					<NavLink to="/">
						Início
					</NavLink>
				</Menu.Item>
				{(
					this.props.permissions.includes("roles.list")
					|| this.props.permissions.includes("log.list")
					|| this.props.permissions.includes("system-log.list")
					|| this.props.permissions.includes("customers.list")
					|| this.props.permissions.includes("users.list")) && (
					<SubMenu key="/administrator" title="Administração" icon={<i className="fal fa-sliders-v" />}>
						{this.props.permissions.includes("roles.list") && <Menu.Item key="/administrator/roles-and-permissions">
							<NavLink to="/administrator/roles-and-permissions">
								Papéis e permissões
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("log.list") && <Menu.Item key="/administrator/logs">
							<NavLink to="/administrator/logs">
								Registros de alterações
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("system-log.list") && <Menu.Item key="/administrator/system-log">
							<NavLink to="/administrator/system-log">
								Registros de erros
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("users.list") && <Menu.Item key="/administrator/users">
							<NavLink to="/administrator/users">
								Usuários administradores
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("settings-general.list")
					|| this.props.permissions.includes("settings-notifications.list")) && (
					<SubMenu key="/settings" title="Configurações" icon={<i className="fal fa-tools" />}>
						{this.props.permissions.includes("settings-notifications.list") && <Menu.Item key="/settings/notifications">
							<NavLink to="/settings/notifications">
								Notificações
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("settings-general.list") && <Menu.Item key="/settings/general">
							<NavLink to="/settings/general">
								Gerais
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("about-company.edit")
					|| this.props.permissions.includes("faq.edit")
					|| this.props.permissions.includes("privacy-policy.edit")
					|| this.props.permissions.includes("terms-of-use.edit")
					|| this.props.permissions.includes("banners.edit")
					|| this.props.permissions.includes("onboarding.list")) && (
					<SubMenu key="/institutional" title="Institucional" icon={<i className="fal fa-file-alt" />}>
						{this.props.permissions.includes("onboarding.list") && <Menu.Item key="/institutional/onboardings">
							<NavLink to="/institutional/onboardings">
								Onboarding
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("privacy-policy.edit") && <Menu.Item key="/institutional/privacy-policy">
							<NavLink to="/institutional/privacy-policy">
								Política de privacidade
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("terms-of-use.edit") && <Menu.Item key="/institutional/terms-of-use">
							<NavLink to="/institutional/terms-of-use">
								Termos de uso
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("grupo-aiz.edit") && <Menu.Item key="/institutional/aiz">
							<NavLink to="/institutional/aiz">
								Grupo AIZ
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("faq.list") && <Menu.Item key="/institutional/faq">
							<NavLink to="/institutional/faq">
								Dúvidas frequentes 
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("banners.list") && <Menu.Item key="/institutional/banners">
							<NavLink to="/institutional/banners">
								Banners
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("news.edit") &&
					this.props.permissions.includes("categorias_treinamento.edit") &&
					this.props.permissions.includes("treinamento.edit") &&
					this.props.permissions.includes("protetores_tela.edit") &&
					this.props.permissions.includes("notifications.edit") &&
					this.props.permissions.includes("perfil_acesso.edit") &&
					this.props.permissions.includes("vale_rio_doce.edit") &&
					this.props.permissions.includes("ver_mais.edit")) && (
					<SubMenu key="/register" title={'Cadastros'} icon={<i className="fal fa-database" />}>
						{this.props.permissions.includes("news.list") && <Menu.Item key="/register/news">
							<NavLink to="/register/news">
								Notícias
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("ver_mais.list") && <Menu.Item key="/register/contents-see-more">
							<NavLink to="/register/contents-see-more">
								Conteúdos ver mais
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("categorias_treinamento.list") && <Menu.Item key="/register/categories-training">
							<NavLink to="/register/categories-training">
								Categorias | Treinamentos 
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("treinamento.list") && <Menu.Item key="/register/trainings">
							<NavLink to="/register/trainings">
								Treinamentos
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("manuais.list") && <Menu.Item key="/register/manuals">
							<NavLink to="/register/manuals">
								Manuais
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("protetores_tela.list") && <Menu.Item key="/register/screen-protectors">
							<NavLink to="/register/screen-protectors">
								Protetores de tela
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("vale_rio_doce.list") && <Menu.Item key="/register/doce-river-valley">
							<NavLink to="/register/doce-river-valley">
								Vale do Rio Doce
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("perfil_acesso.list") && <Menu.Item key="/register/access-profiles">
							<NavLink to="/register/access-profiles">
								Perfis de acesso
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("notifications.list") && <Menu.Item key="/register/notifications">
							<NavLink to="/register/notifications">
								Notificações
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("customers.list") ||
					this.props.permissions.includes("motorcycles.list") ||
					this.props.permissions.includes("vehicles.list") ||
					this.props.permissions.includes("profissionais.list")) && (
					<SubMenu key="/list" title={'Consultas'} icon={<i className="fal fa-search" />}>
						{this.props.permissions.includes("customers.list") && <Menu.Item key="/list/customers">
							<NavLink to="/list/customers">
								Usuários
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("customers-deleted.list")) && (
					<SubMenu key="/list-deleted" title="Excluídos" icon={<i className="fal fa-trash" />}>
						{this.props.permissions.includes("customers-deleted.list") && <Menu.Item key="/list-deleted/customers-deleted">
							<NavLink to="/list-deleted/customers-deleted">
								Usuários
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
				{(
					this.props.permissions.includes("push-general.list")
					|| this.props.permissions.includes("push-city.list")
					|| this.props.permissions.includes("push-state.list")
					|| this.props.permissions.includes("push-user.list")) && (
					<SubMenu key="/push" title={'Push'} icon={<i className="fal fa-bell" />}>
						{this.props.permissions.includes("push-general.list") && <Menu.Item key="/push/general">
							<NavLink to="/push/general">
								Geral
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("push-city.list") && <Menu.Item key="/push/city">
							<NavLink to="/push/city">
								Por cidade
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("push-state.list") && <Menu.Item key="/push/state">
							<NavLink to="/push/state">
								Por estado
							</NavLink>
						</Menu.Item>}
						{this.props.permissions.includes("push-user.list") && <Menu.Item key="/push/user">
							<NavLink to="/push/user">
								Por usuário
							</NavLink>
						</Menu.Item>}
					</SubMenu>
				)}
      </Menu>
    );
  }
}

const mapStateToProps = (state, ownProps) => {
  return {
    permissions: state.auth.userData.permissions,
  };
};

export default connect(mapStateToProps)(withRouter(MainNavigation));
