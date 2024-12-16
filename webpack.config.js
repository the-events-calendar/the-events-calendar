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
 * @type {LocationSchema}
 */
const JsSchema = {
	fileExtensions: ['.js'],
	fileMatcher: (fileAbsolutePath) => !fileAbsolutePath.endsWith('.min.js'),
	getEntryPointName: (fileRelativePath) => 'js/' + fileRelativePath.replace('.js','')
};

/**
 * @type {LocationSchema}
 */
const PostcssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileAbsolutePath, fileName) => !fileName.startsWith('_'),
	getEntryPointName: (fileRelativePath) => 'css/' + fileRelativePath.replace('.pcss','')
};

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
/// END TYSON

// Ideal usage:
// npm i @stellarwp/tyson --save-dev
// tyson init (incl. namespace - dir?) - override in the webpack.config

// @todo namespace for the project from dir or override from command


// This is what would be imported from the `@stellarwp/tyson` package:
// import {JsSchema, PostcssSchema, getLegacyEntryPoints} from '@stellarwp/tyson';

const legacyEntryPoints = getLegacyEntryPoints({
	'/src/resources/js': JsSchema,
	'/src/resources/postcss': PostcssSchema,
});

module.exports = {
	...defaultConfig,
	...{
		entry: (buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...legacyEntryPoints,
			};
		},
	},
};
