import React from "react";
import { useLocation } from "react-router-dom";
import { Button, Dropdown } from "antd";

import UILink from "./UILink";

export default function() {
	let location = useLocation();

	const items = [
		{
			name: 'Sobre',
			url : '/institucional/sobre',
		},
		{
			name: 'Como funciona',
			url : '/institucional/como-funciona',
		},
		{
			name: 'Dúvidas frequentes',
			url : '/institucional/duvidas-frequentes',
		},
		{
			name: 'Política de privacidade',
			url : '/institucional/politica-de-privacidade',
		},
		{
			name: 'Trocas e devolucoes',
			url : '/institucional/trocas-e-devolucoes',
		},
		{
			name: 'Termos de uso',
			url : '/institucional/termos-de-uso',
		},
	];

	const activeItem = items.find(item => item.url === location.pathname);

	const menu = (
		<div className="component-menu-side-menu">
			<ul>
				{items.map((item, i) => {
					return (
						<li key={i} className={item.url === activeItem?.url ? 'active' : ''}>
							<UILink to={item.url} scroll={false}>{item.name}</UILink>
						</li>
					);
				})}
			</ul>
		</div>
	);

	return (
		<div className="component-menu-side">
			{menu}
			<Dropdown trigger="click" overlay={menu} placement="bottomLeft">
				<Button block size="large" className="flex flex-between flex-middle">
					{activeItem?.name ?? 'Selecione'}<i className="icon-arrow-c-down" />
				</Button>
			</Dropdown>
		</div>
	);
}
