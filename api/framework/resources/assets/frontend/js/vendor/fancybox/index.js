import React, { Fragment, useEffect } from "react";
import { Fancybox as NativeFancybox } from "@fancyapps/ui/dist/fancybox.esm";

const options_default = {
	Hash          : false,
	infinite      : false,
	autoFocus     : false,
	placeFocusBack: false,
	Image         : {
		ignoreCoveredThumbnail: true,
		//zoom                  : false,
	},
	Thumbs        : {
		autoStart    : true,
		minSlideCount: 2,
	},
	Html          : {
		youtube: {
			autoplay   : true,
			autohide   : 1,
			fs         : 1,
			rel        : 0,
			hd         : 1,
			wmode      : "transparent",
			enablejsapi: 1,
			html5      : 1,
		},
	},
};

function Fancybox(props) {
	const selector = props?.selector || "[data-fancybox]";

	const options = Object.assign({}, options_default, props?.options);

	useEffect(() => {
		NativeFancybox.bind(selector, options);

		return () => {
			NativeFancybox.unbind(selector);
			NativeFancybox.destroy();
		};
	}, []);

	return <Fragment>{props.children}</Fragment>;
}

export default Fancybox;