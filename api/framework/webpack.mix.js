const mix         = require('laravel-mix');
const public_path = './../';

// Default config
mix.options({
	processCssUrls: false
});

mix.webpackConfig({
	watchOptions: {
		ignored: /node_modules/
	}
});

mix.babelConfig({
	'plugins': ['@babel/plugin-proposal-class-properties']
});

// Set public path
mix.setPublicPath(public_path);

// Disable success notifications
mix.disableNotifications();

// Show config
//console.log(mix.config);

const frontend_path = {
	source: 'resources/assets/frontend/',
	css   : public_path + 'css/frontend/',
	js    : public_path + 'js/frontend/',
	fonts : public_path + 'fonts/frontend/',
	images: public_path + 'images/frontend/',
};

/* ================================
 Frontend
 ================================== */
if( typeof frontend_path !== 'undefined' )
{
	mix
	.less(frontend_path.source + 'less/antd.less', frontend_path.css + 'antd.css', {
		javascriptEnabled: true
	})

	.sass(frontend_path.source + 'sass/app.scss', frontend_path.css + 'app.css')

	.react([
		frontend_path.source + 'js/app.js',
	], frontend_path.js + 'app.js')

	// Copy font's folder
	.copyDirectory(frontend_path.source + 'fonts/', frontend_path.fonts)

	// Copy images's folder
	.copyDirectory(frontend_path.source + 'images/', frontend_path.images)

	.browserSync({
		proxy: process.env.APP_URL,
		files: [
			frontend_path.js + '**/*.js',
			frontend_path.css + '**/*.css',
		]
	});

	if( mix.inProduction() )
	{
		mix.version();
	}
}
