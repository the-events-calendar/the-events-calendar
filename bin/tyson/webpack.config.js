const path = require('path');
// const NodePolyfillPlugin = require('node-polyfill-webpack-plugin');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const {merge} = require('webpack-merge');

module.exports = merge(defaultConfig, {
	entry: './src/index.ts',
	target: 'node', // Ensure Webpack targets the Node.js environment
	output: {
		filename: 'tyson.js',
		path: path.resolve(__dirname, 'dist'),
	},
});
