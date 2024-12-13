const defaultConfig = require( './node_modules/@the-events-calendar/product-taskmaster/config/babelrc.json' );

module.exports = {
	...defaultConfig,
	presets: [
		...defaultConfig.presets,
		'@babel/preset-typescript',
	],
	plugins: [
		...defaultConfig.plugins,
		[
			require.resolve( '@babel/plugin-transform-react-jsx' ),
			{
				runtime: 'automatic',
			},
		],
	],
}
