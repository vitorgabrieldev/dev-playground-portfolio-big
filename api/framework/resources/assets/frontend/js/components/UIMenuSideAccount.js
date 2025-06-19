import React from "react";
import { useDispatch } from "react-redux";
import { useLocation } from "react-router-dom";
import { Button, Dropdown, Modal } from "antd";

import { authActions } from "./../redux/actions";

import UILink from "./UILink";

export default function() {
	const dispatch = useDispatch()
	const location = useLocation();

	const items = [
		{
			name: 'Dados cadastrais',
			url : '/minha-conta',
			icon: 'icon-account-profile',
		},
		{
			name: 'Meus pedidos',
			url : '/minha-conta/meus-pedidos',
			icon: 'icon-account-orders',
		},
		{
			name: 'Meus cartões',
			url : '/minha-conta/meus-cartoes',
			icon: 'icon-account-cards',
		},
		{
			name: 'Meus endereços',
			url : '/minha-conta/meus-enderecos',
			icon: 'icon-account-adresses',
		},
	];

	function doLogout() {
		Modal.confirm({
			title                 : "Deslogar",
			content               : "Deseja sair da sua conta?",
			centered              : true,
			okText                : "Sair",
			autoFocusButton       : null,
			focusTriggerAfterClose: false,
			onOk                  : () => {
				// setTimeout fix enabling modal on close
				setTimeout(() => {
					dispatch(authActions.logout());
				}, 600);
			},
		});
	}

	const activeItem = items.find(item => item.url === location.pathname);

	const menu = (
		<div className="component-menu-side-menu">
			<ul>
				{items.map((item, i) => {
					return (
						<li key={i} className={item.url === activeItem?.url ? 'active' : ''}>
							<UILink to={item.url} scroll={false}>
								<i className={item.icon} />
								{item.name}
							</UILink>
						</li>
					);
				})}
				<li>
					<a onClick={doLogout}>
						<i className="icon-logout-b" />
						Sair
					</a>
				</li>
			</ul>
		</div>
	);

	return (
		<div className="component-menu-side pt-lg-10">
			{menu}
			<Dropdown trigger="click" overlay={menu} placement="bottomLeft">
				<Button block size="large" className="flex flex-middle">
					{!!activeItem?.icon && <i className={activeItem?.icon} />}
					{activeItem?.name ?? 'Selecione'}<i className="icon-arrow-c-down" />
				</Button>
			</Dropdown>
		</div>
	);
}
