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
				{/* Administração */}
				<SubMenu key="/administrator" title="Administração" icon={<i className="fal fa-sliders-v" />}>
					<Menu.Item key="/administrator/roles-and-permissions" icon={<i className="fal fa-user-shield" />}>
						<NavLink to="/administrator/roles-and-permissions">Papéis e permissões</NavLink>
					</Menu.Item>
					<Menu.Item key="/administrator/logs" icon={<i className="fal fa-history" />}>
						<NavLink to="/administrator/logs">Registros de alterações</NavLink>
					</Menu.Item>
					<Menu.Item key="/administrator/system-log" icon={<i className="fal fa-bug" />}>
						<NavLink to="/administrator/system-log">Registros de erros</NavLink>
					</Menu.Item>
					<Menu.Item key="/administrator/users" icon={<i className="fal fa-user-cog" />}>
						<NavLink to="/administrator/users">Usuários administradores</NavLink>
					</Menu.Item>
				</SubMenu>
				{/* Clientes */}
				<SubMenu key="/customers" title="Clientes" icon={<i className="fal fa-users" />}>
					<Menu.Item key="/customers/list" icon={<i className="fal fa-list" />}><NavLink to="/customers/list">Listar clientes</NavLink></Menu.Item>
					<Menu.Item key="/customers/create" icon={<i className="fal fa-user-plus" />}><NavLink to="/customers/create">Cadastrar cliente</NavLink></Menu.Item>
				</SubMenu>
				{/* Cursos */}
				<SubMenu key="/courses" title="Cursos" icon={<i className="fal fa-graduation-cap" />}>
					<Menu.Item key="/courses/list" icon={<i className="fal fa-list" />}><NavLink to="/courses/list">Listar cursos</NavLink></Menu.Item>
					<Menu.Item key="/courses/create" icon={<i className="fal fa-plus" />}><NavLink to="/courses/create">Cadastrar curso</NavLink></Menu.Item>
					<Menu.Item key="/courses/approval" icon={<i className="fal fa-check-circle" />}><NavLink to="/courses/approval">Aprovação de cursos</NavLink></Menu.Item>
					<Menu.Item key="/categories/list" icon={<i className="fal fa-folder-tree" />}><NavLink to="/categories/list">Categorias</NavLink></Menu.Item>
				</SubMenu>
				{/* Aulas/Conteúdos */}
				<SubMenu key="/lessons" title="Aulas & Conteúdos" icon={<i className="fal fa-chalkboard-teacher" />}>
					<Menu.Item key="/lessons/list" icon={<i className="fal fa-list" />}><NavLink to="/lessons/list">Listar aulas</NavLink></Menu.Item>
					<Menu.Item key="/lessons/materials" icon={<i className="fal fa-file-upload" />}><NavLink to="/lessons/materials">Materiais</NavLink></Menu.Item>
				</SubMenu>
				{/* Compras & Transações */}
				<SubMenu key="/transactions" title="Compras & Transações" icon={<i className="fal fa-shopping-cart" />}>
					<Menu.Item key="/transactions/list" icon={<i className="fal fa-list" />}><NavLink to="/transactions/list">Compras realizadas</NavLink></Menu.Item>
					<Menu.Item key="/transactions/details" icon={<i className="fal fa-file-invoice-dollar" />}><NavLink to="/transactions/details">Detalhes de transações</NavLink></Menu.Item>
				</SubMenu>
				{/* Favoritos */}
				<Menu.Item key="/favorites" icon={<i className="fal fa-star" />}><NavLink to="/favorites">Favoritos</NavLink></Menu.Item>
				{/* Progresso */}
				<Menu.Item key="/progress" icon={<i className="fal fa-chart-line" />}><NavLink to="/progress">Progresso de Aprendizagem</NavLink></Menu.Item>
				{/* Avaliações */}
				<Menu.Item key="/reviews" icon={<i className="fal fa-comments" />}><NavLink to="/reviews">Avaliações & Comentários</NavLink></Menu.Item>
				{/* Financeiro */}
				<SubMenu key="/finance" title="Financeiro" icon={<i className="fal fa-wallet" />}>
					<Menu.Item key="/finance/payouts" icon={<i className="fal fa-money-check-alt" />}><NavLink to="/finance/payouts">Saques de produtores</NavLink></Menu.Item>
					<Menu.Item key="/finance/logs" icon={<i className="fal fa-file-alt" />}><NavLink to="/finance/logs">Logs financeiros</NavLink></Menu.Item>
					<Menu.Item key="/finance/balances" icon={<i className="fal fa-balance-scale" />}><NavLink to="/finance/balances">Saldos de produtores</NavLink></Menu.Item>
					<Menu.Item key="/finance/payment-methods" icon={<i className="fal fa-credit-card" />}><NavLink to="/finance/payment-methods">Métodos de pagamento</NavLink></Menu.Item>
				</SubMenu>
				{/* Notificações */}
				<SubMenu key="/notifications" title="Notificações" icon={<i className="fal fa-bell" />}>
					<Menu.Item key="/notifications/send" icon={<i className="fal fa-paper-plane" />}><NavLink to="/notifications/send">Enviar notificação</NavLink></Menu.Item>
					<Menu.Item key="/notifications/history" icon={<i className="fal fa-history" />}><NavLink to="/notifications/history">Histórico de notificações</NavLink></Menu.Item>
					<Menu.Item key="/notifications/templates" icon={<i className="fal fa-file-alt" />}><NavLink to="/notifications/templates">Templates</NavLink></Menu.Item>
				</SubMenu>
				{/* FAQ */}
				<SubMenu key="/faq" title="FAQ" icon={<i className="fal fa-question-circle" />}>
					<Menu.Item key="/faq/list" icon={<i className="fal fa-list" />}><NavLink to="/faq/list">Perguntas frequentes</NavLink></Menu.Item>
					<Menu.Item key="/faq/categories" icon={<i className="fal fa-folder-open" />}><NavLink to="/faq/categories">Categorias de FAQ</NavLink></Menu.Item>
				</SubMenu>
				{/* Políticas & Institucional */}
				<SubMenu key="/institutional" title="Políticas & Institucional" icon={<i className="fal fa-file-contract" />}>
					<Menu.Item key="/institutional/terms" icon={<i className="fal fa-file-signature" />}><NavLink to="/institutional/terms">Termos de uso</NavLink></Menu.Item>
					<Menu.Item key="/institutional/privacy" icon={<i className="fal fa-user-secret" />}><NavLink to="/institutional/privacy">Política de privacidade</NavLink></Menu.Item>
					<Menu.Item key="/institutional/banners" icon={<i className="fal fa-image" />}><NavLink to="/institutional/banners">Banners</NavLink></Menu.Item>
				</SubMenu>
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
