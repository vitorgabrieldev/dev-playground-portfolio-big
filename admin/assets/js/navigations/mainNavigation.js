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
						{this.props.permissions.includes("settings-general.list") && <Menu.Item key="/settings/general">
							<NavLink to="/settings/general">
								Gerais
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
