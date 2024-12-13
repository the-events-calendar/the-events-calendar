/**
 * External dependencies
 */
const { resolve } = require('path');
const { reduce, zipObject } = require('lodash');
const merge = require('webpack-merge');
const common = require('@the-events-calendar/product-taskmaster/webpack/common/webpack.config');
const {
	getDirectoryNames,
	getDirectories,
} = require('@the-events-calendar/product-taskmaster/webpack/utils/directories');
const {
	getJSFileNames,
	getJSFiles,
} = require('@the-events-calendar/product-taskmaster/webpack/utils/files');

const PLUGIN_SCOPE = 'events';

//
// ────────────────────────────────────────────────────────────────────────────────────── I ──────────
//   :::::: G E N E R A T E   E V E N T S   P L U G I N : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────────────────────
//

/**
 * By default, the optimization would break all modules from the `node_modules` directory
 * in a `src/resources/js/app/vendor.js` file. That file would include React and block-editor
 * dependencies that are not always required on the frontend. This modification of the default
 * optimization will create two files: one (`src/resources/js/app/vendor-babel.js`) that contains
 * only the Babel transpilers and one (`src/resources/js/app/vendor.js`) that contains all the
 * other dependencies. The second file (`src/resources/js/app/vendor.js`) MUST require the first
 * (`src/resources/js/app/vendor-babel.js`) file as a dependency.
 */
common.optimization.splitChunks.cacheGroups['vendor-babel-runtime'] = {
	name: 'vendor-babel',
	chunks: 'all',
	test: /[\\/]node_modules[\\/]@babel[\\/]/,
	priority: 20,
};
common.optimization.splitChunks.cacheGroups.vendor.priority = 10;

const isProduction = process.env.NODE_ENV === 'production';
const postfix = isProduction ? 'min.css' : 'css';

// The targets we would like to compile.
// The `moveFromTo` property is used to move the files in place after the build completed using the
// `MoveTargetsInPlace` plugin; see below.
const targets = [
	{
		name: 'main',
		entry: './src/modules/index.js',
		outputScript: './src/resources/js/app/main.min.js',
		outputStyle: `src/resources/css/app/[name].${postfix}`,
	},
	{
		name: 'widgets',
		entry: './src/modules/widgets/index.js',
		outputScript: './src/resources/js/app/main.min.js',
		outputStyle: `src/resources/css/app/[name].${postfix}`,
	},
	{
		name: 'tec-events-onboarding-wizard-script',
		entry: './src/resources/packages/wizard/index.tsx',
		outputScript: './build/Wizard/onboarding.min.js',
		outputStyle: `build/Wizard/onboarding.${postfix}`,
		moveFromTo: {
			'src/resources/js/app/tec-events-onboarding-wizard-script.js':
				'build/Seating/onboarding.js',
			'src/resources/css/app/tec-events-onboarding-wizard-script.css':
				'build/Seating/onboarding.css',
		},
	},
];

// A function cannot be spread directly, we need this temporary variable.
const targetEntries = reduce(
	targets,
	(carry, target) => ({
		...carry,
		[target.name]: resolve(__dirname, target.entry),
	}),
	{}
);

const config = merge(common, {
	// Add externals missing from products-taskmaster.
	externals: [
		{
			'@wordpress/core-data': 'wp.coreData',
		},
	],
	// Configure multiple entry points.
	entry: targetEntries,
});

// WebPack 4 does support multiple entry and output points, but the plugins used by the build do not.
// For this reason we're setting the output target to a string template.
// The files will be moved to the correct location after the build completed, by the `MoveTargetsInPlace` plugin.
// See below.
config.output = {
	path: __dirname,
	filename: './src/resources/js/app/[name].min.js',
};

config.resolve = {
	...config.resolve,
	alias: {
		'lodash-es': 'lodash',
	},
	extensions: [ '.jsx', '.ts', '.tsx', '...', '.js' ]
};

config.module.rules.push({
	test: /\.m?(j|t)sx?$/,
	exclude: /node_modules/,
	use: [
		{
			loader: require.resolve( 'babel-loader' ),
			options: {
				// Babel uses a directory within local node_modules
				// by default. Use the environment variable option
				// to enable more persistent caching.
				cacheDirectory:
					process.env.BABEL_CACHE_DIRECTORY || true,
			},
		},
	],
});

// Define, build and add to the stack of plugins a plugin that will move the files in place after they are built.
const fs = require('node:fs');
const normalize = require('path').normalize;

class MoveTargetsInPlace {
	constructor(moveTargets) {
		// Add, to each move target, the minified version of the file.
		Object.keys(moveTargets).forEach((file) => {
			const minFile = file.replace(/\.(js|css)/g, '.min.$1');
			moveTargets[minFile] = moveTargets[file].replace(
				/\.(js|css)/i,
				'.min.$1'
			);
		});
		this.moveTargetsObject = moveTargets;
		this.sourceFiles = Object.keys(moveTargets).map((file) =>
			normalize(file)
		);
		this.moveFile = this.moveFile.bind(this);
	}

	moveFile(file) {
		const normalizedFile = normalize(file);

		if (this.sourceFiles.indexOf(normalizedFile) === -1) {
			return;
		}

		const destination = this.moveTargetsObject[normalizedFile];
		console.log(`Moving ${normalizedFile} to ${destination}...`);

		// Recursively create the directory for the target.
		fs.mkdirSync(destination.replace(/\/[^/]+$/, ''), { recursive: true });

		// Move the target.
		fs.renameSync(normalizedFile, destination);
	}

	apply(compiler) {
		// compiler.hooks.done.tap ( 'MoveTargetsIntoPlace', this.moveTargets );
		compiler.hooks.assetEmitted.tap('MoveTargetsIntoPlace', this.moveFile);
	}
}

const moveTargets = targets.reduce((carry, target) => {
	return {
		...carry,
		...target.moveFromTo,
	};
}, {});
config.plugins.push(new MoveTargetsInPlace(moveTargets));

// If COMPILE_SOURCE_MAPS env var is set, then set devtool=eval-source-map
if (process.env.COMPILE_SOURCE_MAPS) {
	config.devtool = 'eval-source-map';
}

//
// ──────────────────────────────────────────────────────────────────────────────────────────── II ──────────
//   :::::: G E N E R A T E   S T Y L E S   F R O M   V I E W S : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────────────────────────────
//

const stylePath = resolve(__dirname, './src/styles');
const styleDirectories = getDirectories(stylePath);
const styleDirectoryNames = getDirectoryNames(stylePath);
const styleEntries = zipObject(styleDirectoryNames, styleDirectories);

const removeExtension = (str) => str.slice(0, str.lastIndexOf('.'));

const entries = reduce(
	styleEntries,
	(result, dirPath, dirName) => {
		const jsFiles = getJSFiles(dirPath);
		const jsFileNames = getJSFileNames(dirPath);
		const entryNames = jsFileNames.map(
			(filename) => `${dirName}/${removeExtension(filename)}`
		);
		return {
			...result,
			...zipObject(entryNames, jsFiles),
		};
	},
	{}
);

const styleConfig = merge(common, {
	entry: entries,
	output: {
		path: __dirname,
	},
});

//
// ─── EXPORT CONFIGS ─────────────────────────────────────────────────────────────
//

module.exports = [config, styleConfig];
