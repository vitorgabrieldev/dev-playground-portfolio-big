import React, { Fragment } from "react";
import * as PropTypes from "prop-types";
import { Breadcrumb } from 'antd';

import UILink from "./UILink";

function UIBreadcrumbs(props) {
	const {separator, items, linkScroll} = props;

	return (
		<div className="component-breadcrumbs">
			<Breadcrumb separator="">
				<Breadcrumb.Item>
					<UILink to="/" scroll={linkScroll}>
						<span>Início</span>
					</UILink>
				</Breadcrumb.Item>
				{items.map((item, i) => (
					<Fragment key={i}>
						<Breadcrumb.Separator>
							<i className={separator} />
						</Breadcrumb.Separator>
						<Breadcrumb.Item>
							{item.url ? (
								<UILink to={item.url} scroll={linkScroll}>
									{!!item.icon && <i className={item.icon} />}
									<span>{item.name}</span>
								</UILink>
							) : (
								<Fragment>
									<span>{item.name}</span>
								</Fragment>
							)}
						</Breadcrumb.Item>
					</Fragment>
				))}
			</Breadcrumb>
		</div>
	);
}

UIBreadcrumbs.propTypes = {
	separator : PropTypes.string,
	items     : PropTypes.arrayOf(
		PropTypes.shape({
			name: PropTypes.string.isRequired,
			url : PropTypes.string,
			icon: PropTypes.string,
		}),
	),
	linkScroll: PropTypes.bool,
}

UIBreadcrumbs.defaultProps = {
	separator : "icon-arrow-c-right",
	items     : [],
	linkScroll: false,
}

export default UIBreadcrumbs;