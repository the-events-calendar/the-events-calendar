module.exports = {
	presets: [
		[
			'@wordpress/babel-preset-default/',
			{
				targets: {
					node: 'current',
				},
			},
		],
	],
	// This is needed to transform ES modules
	env: {
		test: {
			plugins: [ '@babel/plugin-transform-modules-commonjs' ],
		},
	},
};
