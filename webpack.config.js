/// START TYSON
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const {readdirSync, statSync, existsSync} = require('fs');
const {dirname, basename,extname} = require('path');

/**
 * @typedef {Object} LocationSchema
 * @property {string[]} fileExtensions The extensions of the files to match.
 * @property {(fileName: string, fileRelativePath: string, fileAbsolutePath: string) => boolean} fileMatcher A function that returns true if the file matches the schema.
 * @property {(fileRelativePath: string) => string} getEntryPointName A function that returns the entry point name for the file.
 * @property {boolean} recursive Whether to search the directory recursively for entry points.
 */

/**
 * @typedef {Object<string, LocationSchema>} Locations
 */

/**
 *
 * @param {Locations} locations A list of directories to search for entry points recursively.
 */
function getLegacyEntryPoints(locations) {
	const entries = {};
	Object.keys(locations).forEach((fileRelativePath) => {
		const schema = locations[fileRelativePath];
		const fileExtensions = schema.fileExtensions;
		const fileMatcher = schema.fileMatcher;
		const locationAbsolutePath = __dirname + fileRelativePath;

		if (!existsSync(locationAbsolutePath)) {
			return;
		}

		const files = readdirSync(locationAbsolutePath, {recursive: true});

		files.forEach((file) => {
			const fileAbsolutePath = locationAbsolutePath + '/' + file;

			// If the file is a directory, skip it.
			if (statSync(fileAbsolutePath).isDirectory()) {
				return;
			}

			const fileExtension = extname(file);

			// If the file extension is not among the ones we care about, skip it.
			if (!fileExtensions.includes(fileExtension)) {
				return;
			}

			const fileName = basename(fileAbsolutePath);
			if (!fileMatcher(fileName, fileRelativePath, fileAbsolutePath)) {
				return;
			}

			entries[schema.getEntryPointName(file)] = fileAbsolutePath;
		});
	});

	return entries;
}

/**
 * @type {LocationSchema}
 */
const TECLegacyJsSchema = {
	fileExtensions: ['.js'],
	fileMatcher: (filename) => !filename.endsWith('.min.js'),
	getEntryPointName: (fileRelativePath) => 'js/' + fileRelativePath.replace('.js','')
};

/**
 * @type {LocationSchema}
 */
const TECLegacyPostcssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileName) => !fileName.startsWith('_'),
	getEntryPointName: (fileRelativePath) => 'css/' + fileRelativePath.replace('.pcss','')
};

/**
 * @type {LocationSchema}
 */
const TECLegacyBlocksFrontendPcssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileName) => fileName === 'frontend.pcss',
	getEntryPointName: (fileRelativePath) => 'app/' + basename(dirname(fileRelativePath)) + '/frontend.css'
};

const TECPackageSchema = {
	fileExtensions: ['.js', '.jsx', '.ts', '.tsx'],
	fileMatcher: (fileName, fileRelativePath) => fileName.match(/index\.(js|jsx|ts|tsx)$/),
	getEntryPointName: (fileRelativePath) => dirname(fileRelativePath)
}
/// END TYSON

// Ideal usage:
// npm i @stellarwp/tyson --save-dev
// tyson init (incl. namespace - dir?) - override in the webpack.config


// This is what would be imported from the `@stellarwp/tyson` package:
// import {JsSchema, PostcssSchema, getLegacyEntryPoints} from '@stellarwp/tyson';

const legacyEntryPoints = getLegacyEntryPoints({
	'/src/resources/js': TECLegacyJsSchema,
	'/src/resources/postcss': TECLegacyPostcssSchema,
	'/src/styles': TECLegacyBlocksFrontendPcssSchema,
	'/src/resources/packages': TECPackageSchema,
});
// Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
legacyEntryPoints['app/main.js'] = __dirname + '/src/modules/index.js';
legacyEntryPoints['app/widgets.js'] = __dirname + '/src/modules/widgets/index.js';

module.exports = {
	...defaultConfig,
	...{
		entry:(buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...legacyEntryPoints
			};
		},
	},
};

// @todo namespace for the project from dir or override from command
// @todo what to do with images moved/copied to /build/images?
