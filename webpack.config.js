/// START TYSON
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const fs = require('fs');
const path = require('path');

/**
 * @typedef {Object} LocationSchema
 * @property {string[]} fileExtensions The extensions of the files to match.
 * @property {(fileAbsolutePath: string, fileName: string) => boolean} fileMatcher A function that returns true if the file matches the schema.
 * @property {(fileRelativePath: string) => string} getEntryPointName A function that returns the entry point name for the file.
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
	Object.keys(locations).forEach((locationRelativePath) => {
		const schema = locations[locationRelativePath];
		const fileExtensions = schema.fileExtensions;
		const fileMatcher = schema.fileMatcher;
		const locationAbsolutePath = __dirname + locationRelativePath;

		if (!fs.existsSync(locationAbsolutePath)) {
			return;
		}

		const files = fs.readdirSync(locationAbsolutePath, {recursive: true});

		files.forEach((file) => {
			const fileAbsolutePath = locationAbsolutePath + '/' + file;

			// If the file is a directory, skip it.
			if (fs.statSync(fileAbsolutePath).isDirectory()) {
				return;
			}

			const fileExtension = path.extname(file);

			// If the file extension is not among the ones we care about, skip it.
			if (!fileExtensions.includes(fileExtension)) {
				return;
			}

			const fileName = path.basename(fileAbsolutePath);
			if (!fileMatcher(fileAbsolutePath, fileName)) {
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
	fileMatcher: (fileAbsolutePath) => !fileAbsolutePath.endsWith('.min.js'),
	getEntryPointName: (fileRelativePath) => 'js/' + fileRelativePath.replace('.js','')
};

/**
 * @type {LocationSchema}
 */
const TECLegacyPostcssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileAbsolutePath, fileName) => !fileName.startsWith('_'),
	getEntryPointName: (fileRelativePath) => 'css/' + fileRelativePath.replace('.pcss','')
};

/**
 * @type {LocationSchema}
 */
const TECLegacyBlocksFrontendPcssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileAbsolutePath, fileName) => fileName === 'frontend.pcss',
	getEntryPointName: (fileRelativePath) => 'app/' + path.basename(path.dirname(fileRelativePath)) + '/frontend.css'
};
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
});
// Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
legacyEntryPoints['app/main.js'] = __dirname + '/src/modules/index.js';

module.exports = {
	...defaultConfig,
	...{
		entry: (buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...legacyEntryPoints
			};
		},
	},
};

// @todo namespace for the project from dir or override from command
// @todo what to do with images moved/copied to /build/images?
