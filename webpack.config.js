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
 * Compiles a list of entry points for `@wordpress/scripts` to build.
 *
 * @param {Locations} locations A list of directories to search for entry points recursively.
 *
 * @return {Object<string,string>} A map from entry points to the file to build.
 */
function compileCustomEntryPoints(locations) {
	const entries = {};
	Object.keys(locations).forEach(( location) => {
		const schema = locations[ location];
		const fileExtensions = schema.fileExtensions;
		const fileMatcher = schema.fileMatcher;
		const locationAbsolutePath = __dirname +  location;

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

			const fileRelativePath = fileAbsolutePath.replace(locationAbsolutePath, '');
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
 * A list of package roots discovered so far.
 *
 * @type {string[]}
 */
const packageRoots = [];

/**
 * Returns whether a file is a package index file or not.
 * Package index files are index files that are not found in sub-directories of previously discovered packages.
 * This function leverages the fact that the node `readdir` function will scan directories depth-first.
 *
 * @param {string} fileRelativePath The file path relative to the schema location.
 *
 * @returns {boolean} Whether the file is a package index file or not.
 */
function isPackageRootIndex(fileRelativePath) {
	const dirFrags = dirname(fileRelativePath).split('/').filter((frag) => frag !== '').reverse();
	let curDir = dirFrags.pop();
	let prevDir = null;
	while (dirFrags.length !== 0 && prevDir !== curDir) {
		if (packageRoots.includes(curDir)) {
			return false;
		}
		prevDir = curDir;
		curDir += '/' + dirFrags.pop();
	}

	return true;
}

/**
 * The Events Calendar legacy JavaScript asset files.
 * Javascript files are in `/src/resources/js` and each file should be built.
 *
 * @type {LocationSchema}
 */
const TECLegacyJsSchema = {
	fileExtensions: ['.js'],
	fileMatcher: (filename) => !filename.endsWith('.min.js'),
	getEntryPointName: (fileRelativePath) => 'js/' + fileRelativePath.replace('.js','')
};

/**
 * The Events Calendar legacy PostCSS schema.
 * PostCSS files are in `/src/resources/postcss`, index files are those not prefixed with `_`.
 *
 * @type {LocationSchema}
 */
const TECPostCssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileName) => !fileName.startsWith('_'),
	getEntryPointName: (fileRelativePath) => 'css/' + fileRelativePath.replace('.pcss','')
};

/**
 * The Events Calendar legacy Blocks schema to build front-end CSS files.
 * Blocks are in `/src/modules` and their style are in `/src/styles`, by block.
 *
 * @type {LocationSchema}
 */
const TECLegacyBlocksFrontendPostCssSchema = {
	fileExtensions: ['.pcss'],
	fileMatcher: (fileName) => fileName === 'frontend.pcss',
	getEntryPointName: (fileRelativePath) => 'app/' + basename(dirname(fileRelativePath)) + '/frontend.css'
};

/**
 * The Events Calendar non-block package schema.
 * Packages are in `/src/packages` and each define a root index file. Sub-directory index files are ignored.
 *
 * @type {LocationSchema}
 */
const TECPackageSchema = {
	fileExtensions: ['.js', '.jsx', '.ts', '.tsx'],
	fileMatcher: (fileName, fileRelativePath, fileAbsolutePath) => fileName.match(/index\.(js|jsx|ts|tsx)$/) && isPackageRootIndex(fileRelativePath),
	getEntryPointName: (fileRelativePath) => {
		packageRoots.push(dirname(fileRelativePath));
		return dirname(fileRelativePath);
	}
}
/// END TYSON

// Ideal usage:
// npm i @stellarwp/tyson --save-dev
// tyson init (incl. namespace - dir?) - override in the webpack.config


// This is what would be imported from the `@stellarwp/tyson` package:
// import {TECLegacyJsSchema, TECPostCssSchema, TECLegacyBlocksFrontendPostCssSchema, TECPackageSchema, compileCustomEntryPoints} from '@stellarwp/tyson';

const customEntryPoints = compileCustomEntryPoints({
	'/src/resources/js': TECLegacyJsSchema,
	'/src/resources/postcss': TECPostCssSchema,
	'/src/styles': TECLegacyBlocksFrontendPostCssSchema,
	'/src/resources/packages': TECPackageSchema,
});
// Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
customEntryPoints['app/main'] = __dirname + '/src/modules/index.js';
customEntryPoints['app/widgets'] = __dirname + '/src/modules/widgets/index.js';

module.exports = {
	...defaultConfig,
	...{
		entry:(buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...customEntryPoints
			};
		},
	},
};

// @todo namespace for the project from dir or override from command
// @todo what to do with images moved/copied to /build/images?
// @todo what to do with -rtl styles?
