import React, { PureComponent } from "react";
import * as PropTypes from "prop-types";
import { Button } from "antd";

import UIImage from "./UIImage";
import UILink from "./UILink";

import { number_format } from "./../helpers/phpjs";

class UIProduct extends PureComponent {
	static propTypes = {
		id            : PropTypes.string.isRequired,
		name          : PropTypes.string.isRequired,
		url           : PropTypes.string,
		slug          : PropTypes.string.isRequired,
		thumb         : PropTypes.any,
		thumbPreloader: PropTypes.any,
		price         : PropTypes.number.isRequired,
		priceOld      : PropTypes.any,
		hasStock      : PropTypes.bool,
		buyText       : PropTypes.string.isRequired,
		insideSlide   : PropTypes.bool.isRequired,
	};

	static defaultProps = {
		hasStock   : true,
		buyText    : 'Compre agora',
		insideSlide: false,
	}

	render() {
		const {name, url, slug, thumb, thumbPreloader, price, priceOld, hasStock, buyText, insideSlide} = this.props;

		return (
			<UILink to={url ? url : `/produto/${slug}`} className={`products-item ${thumb ? 'with-image' : 'no-image'} ${hasStock ? 'with-stock' : 'no-stock'}`} insideSlide={insideSlide}>
				<figure>
					<UIImage src={thumb ? thumb : 'images/frontend/no-image.png'} preloader={thumbPreloader ? thumbPreloader : 'images/frontend/no-image.png'} />
				</figure>
				<h3>{name}</h3>
				{!!priceOld && <div className="price-old-wrap">
					<span className="stamp-offer">{`${Math.floor(((priceOld - price) / priceOld) * 100)}% OFF`}</span>
					<span className="price-old">{`R$ ${number_format(priceOld, 2, ',', '.')}`}</span>
				</div>}
				<div className="price">{`R$ ${number_format(price, 2, ',', '.')}`}</div>
				{hasStock ? (
					<div className="btn-buy-wrap">
						<Button type="primary" className="btn-buy" block icon={<i className="icon-cart" />}>{buyText}</Button>
					</div>
				) : (
					<div className="unavailable">Indisponível</div>
				)}
			</UILink>
		)
	}
}

export default UIProduct;
