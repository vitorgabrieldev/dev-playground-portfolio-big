import React, { Component } from "react";
import * as PropTypes from "prop-types";

class UIImage extends Component {
	static propTypes = {
		src      : PropTypes.string.isRequired,
		preloader: PropTypes.string,
		alt      : PropTypes.string,
		className: PropTypes.string,
	};

	static defaultProps = {
		alt: '',
	};

	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			image  : this.props.preloader,
		};

		this.loadingImage = null;
	}

	componentDidMount() {
		this.fetchImage(this.props.src)
	}

	componentDidUpdate(prevProps) {
		if( prevProps.src !== this.props.src )
		{
			this.setState({
				image  : prevProps.preloader,
				loading: true,
			}, () => {
				this.fetchImage(prevProps.src);
			})
		}
	}

	componentWillUnmount() {
		if( this.loadingImage )
		{
			this.loadingImage.onload = null
		}
	}

	fetchImage = src => {
		const image  = new Image();
		image.onload = () => {
			this.setState({
				image  : this.loadingImage.src,
				loading: false,
			})
		};
		image.src    = src;

		this.loadingImage = image;
	}

	render() {
		const {loading, image} = this.state

		const {src, preloader, alt, style, ...rest} = this.props

		return <img
			src={image}
			data-preloader={preloader}
			alt={alt}
			{...rest}
			style={{
				...style,
				filter    : loading ? "blur(20px)" : "none",
				transition: loading ? "none" : "filter 300ms ease-out"
			}}
		/>
	}
}

export default UIImage;