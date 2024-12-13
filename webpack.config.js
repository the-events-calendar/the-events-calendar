// npm i @stellarwp/tyson --save-dev
// echo 'module.exports = require( \'@stellarwp/tyson/config/webpack.config\' );' > webpack.config.js
// @todo namespace for the project from dir or override from command
// tyson init (incl. namespace - dir?) - override in the webpack.config

// @todo when compiling CSS do not create an empty JS file.

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	...{
		entry: (buildType)=> {
			const defaultEntryPoints =  defaultConfig.entry(buildType)
			const legacyEntryPoints = {
				'notice-install-event-tickets-script': __dirname + '/src/resources/js/admin/notice-install-event-tickets.js',
				'notice-install-event-tickets-style': __dirname + '/src/resources/postcss/admin/notice-install-event-tickets.pcss'
			}
			return {...defaultEntryPoints,...legacyEntryPoints}
		}
	}
}
