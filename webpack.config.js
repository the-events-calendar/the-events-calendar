/// START TYSON
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const {readdirSync, statSync, existsSync} = require('fs');
const {dirname, basename, extname} = require('path');

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

/// Utilities ///

/**
 * Prepends a rule to another rule in the WebPack configuration.
 *
 * @param {Object} config  WebPack configuration.
 * @param {Object} rule    Rule to prepend.
 * @param {function(rule: string): boolean} ruleMatcher A function that will be used to find the rule to prepend the rule to.
 *
 * @return {void} The configuration is modified in place.
 */
function prependRuleToRuleInConfig(config, rule, ruleMatcher) {
	// Run direct access on the configuration: if the schema does not match this should crash.
	const ruleIndex = config.module.rules.findIndex(ruleMatcher);

	if (ruleIndex === undefined) {
		throw new Error('No matching rule found');
	}

	config.module.rules.splice(ruleIndex, 0, rule);
}

/**
 * Returns whether an object following the `module.rules` WebPack schema configuration format uses a loader or not.
 *
 * The loader could be still unresolved (e.g. `some-loader`) or resolved to an absolute path
 * (e.g. `/home/User/some-loader/dist/index.js`). For this reason the comparison is not a strict ones,
 * but a `loader.includes(candidate)` one.
 *
 * @param {Object} rule      A rule in the `module.rules` WebPack schema configuration format to check.
 * @param {string} loader    The name of a loader to check.
 *
 * @returns {boolean} Whether the specified rule uses the specified loader or not.
 */
function ruleUsesLoader(rule, loader) {
	if (!rule.use) {
		// Not all rules will define a `use` property, so we can simply return false here.
		return false;
	}

	// The rule.use property is a string.
	if (typeof rule.use === 'string' && rule.use.includes(loader)) {
		return true;
	}

	if (!Array.isArray(rule.use)) {
		// If it's not an array, we cannot continue searching for our loader, so we can return false here.
		return false;
	}

	for (let i = 0; i < rule.use.length; i++) {
		const use = rule.use[i];

		if (typeof use === 'string') {
			if (use.includes(loader)) {
				return true;
			}

			continue;
		}

		if (typeof use === 'object') {
			if (use?.loader?.includes(loader)) {
				return true;
			}
			continue;
		}
	}

	return false;
}

/**
 * Compiles a list of entry points for `@wordpress/scripts` to build.
 *
 * @param {Locations} locations A list of directories to search for entry points recursively.A
 * @param {Object} config The webpack configuration object.
 *
 * @return {Object<string,string>} A map from entry points to the file to build.
 */
function compileCustomEntryPoints(locations, config) {
	const entries = {};
	Object.keys(locations).forEach((location) => {
		const schema = locations[location];
		const fileExtensions = schema.fileExtensions;
		const fileMatcher = schema.fileMatcher;
		const locationAbsolutePath = __dirname + location;

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

			if (schema.modifyConfig) {
				// Modify the current WebPack configuration by reference.
				schema.modifyConfig(config);
			}
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

/// Schemas ///

/**
 * The Events Calendar legacy JavaScript asset files.
 * Javascript files are in `/src/resources/js` and each file should be built.
 *
 * @type {LocationSchema}
 */
const TECLegacyJsSchema = {
	fileExtensions: ['.js'],
	fileMatcher: (filename) => !filename.endsWith('.min.js'),
	getEntryPointName: (fileRelativePath) => 'js/' + fileRelativePath.replace('.js', ''),
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
	getEntryPointName: (fileRelativePath) => 'css/' + fileRelativePath.replace('.pcss', ''),
	/**
	 * PostCSS files in the `src/modules/blocks` directory use PostCSS nesting, where `&` indicates "this".
	 * By default WordPress scripts would use new CSS nesting syntax where `&` indicates the parent.
	 * We add here the `postcss-nested` plugin to allow the use of `&` to mean "this".
	 * In webpack loaders are applied in LIFO order: this will prepare the PostCSS for the default `postcss-loader`.
	 */
	modifyConfig: (config) => config.module.rules.push(
		{
			test: /src\/modules\/blocks\/.*?\.pcss$/,
			use: [
				{
					loader: 'postcss-loader',
					options: {
						postcssOptions: {
							plugins: [
								'postcss-nested',
							],
						},
					},
				},
			],
		},
	),
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
	getEntryPointName: (fileRelativePath) => 'app/' + basename(dirname(fileRelativePath)) + '/frontend',
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
	},
};
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
}, defaultConfig);
// Blocks from `/src/modules/index.js` are built to `/build/app/main.js`.
customEntryPoints['app/main'] = __dirname + '/src/modules/index.js';
customEntryPoints['app/widgets'] = __dirname + '/src/modules/widgets/index.js';

/*
 * Prepends a loader for SVG files that will be applied after the default one. Loaders are applied
 * in a LIFO queue in WebPack.
 * By default `@wordpress/scripts` uses `@svgr/webpack` to handle SVG files and, together with it,
 * the default SVGO (package `svgo/svgo-loader`) configuration that includes the `prefixIds` plugin.
 * To avoid `id` and `class` attribute conflicts, the `prefixIds` plugin would prefix all `id` and
 * `class` attributes in SVG tags with a generated prefix. This would break TEC classes (already
 * namespaced) so here we prepend a rule to handle SVG files in the `src/modules` directory by
 * disabling the `prefixIds` plugin.
 */
prependRuleToRuleInConfig(defaultConfig, {
	test: /\/src\/modules\/.*?\.svg$/,
	issuer: /\.(j|t)sx?$/,
	use: [
		{
			loader: '@svgr/webpack',
			options: {
				svgoConfig: {
					plugins: [
						{
							name: 'prefixIds',
							params: {
								prefixIds: false,
								prefixClassNames: false,
							},
						},
					],
				},
			},
		},
		{
			loader: 'url-loader',
		},
	],
	type: 'javascript/auto',
}, (rule) => ruleUsesLoader(rule, '@svgr/webpack'));

module.exports = {
	...defaultConfig,
	...{
		entry: (buildType) => {
			const defaultEntryPoints = defaultConfig.entry(buildType);
			return {
				...defaultEntryPoints, ...customEntryPoints,
			};
		},
	},
};

// @todo namespace for the project from dir or override from command
// @todo what to do with images moved/copied to /build/images?
// @todo what to do with -rtl styles?
