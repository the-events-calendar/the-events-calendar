/**
 * External dependencies
 */
const { resolve } = require( 'path' );
const { reduce, zipObject } = require( 'lodash' );
const merge = require( 'webpack-merge' );
const common = require( '@the-events-calendar/product-taskmaster/webpack/common/webpack.config' );
const { getDirectoryNames, getDirectories } = require( '@the-events-calendar/product-taskmaster/webpack/utils/directories' );
const { getJSFileNames, getJSFiles } = require( '@the-events-calendar/product-taskmaster/webpack/utils/files' );

const PLUGIN_SCOPE = 'events';

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

//
// ────────────────────────────────────────────────────────────────────────────────────── I ──────────
//   :::::: G E N E R A T E   E V E N T S   P L U G I N : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────────────────────
//

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

const moveTargets = targets.reduce((carry, target) => {
	return {
		...carry,
		...target.moveFromTo,
	};
}, {});

const config = merge( common, {
	// Add externals missing from products-taskmaster.
	externals: [
		{
			'@wordpress/core-data': 'wp.coreData',
			'@wordpress/edit-post': 'wp.editPost',
			'@tec/tickets/seating/service/iframe':
				'tec.tickets.seating.service.iframe',
			'@tec/tickets/seating/service/errors':
				'tec.tickets.seating.service.errors',
			'@tec/tickets/seating/service/notices':
				'tec.tickets.seating.service.notices',
			'@tec/tickets/seating/service': 'tec.tickets.seating.service',
			'@tec/tickets/seating/service/api':
				'tec.tickets.seating.service.api',
			'@tec/tickets/seating/utils': 'tec.tickets.seating.utils',
			'@tec/tickets/seating/ajax': 'tec.tickets.seating.ajax',
			'@tec/tickets/seating/currency': 'tec.tickets.seating.currency',
			'@tec/tickets/seating/frontend/session':
				'tec.tickets.seating.frontend.session',
			'@tec/tickets/order-modifiers/rest': 'tec.tickets.orderModifiers.rest',
		},
	],
	// Configure multiple entry points.
	entry: targetEntries,
	output: {
		path: __dirname,
		filename: './src/resources/js/app/[name].min.js',
	},
} );

config.plugins.push(new MoveTargetsInPlace(moveTargets));

//
// ────────────────────────────────────────────────────────────────────────────────────── II ──────────
//   :::::: G E N E R A T E   W I D G E T S   P L U G I N : :  :   :    :     :        :          :
// ────────────────────────────────────────────────────────────────────────────────────────────────
//

const widgetsConfig = merge( common, {
	entry: {
		widgets: resolve( __dirname, './src/modules/widgets/index.js' ),
	},
	output: {
		path: __dirname,
		library: [ 'tribe', PLUGIN_SCOPE, '[name]' ],
	},
} );

//
// ──────────────────────────────────────────────────────────────────────────────────────────── III ──────────
//   :::::: G E N E R A T E   S T Y L E S   F R O M   V I E W S : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────────────────────────────
//

const stylePath = resolve( __dirname, './src/styles' );
const styleDirectories = getDirectories( stylePath );
const styleDirectoryNames = getDirectoryNames( stylePath );
const styleEntries = zipObject( styleDirectoryNames, styleDirectories );

const removeExtension = ( str ) => str.slice( 0, str.lastIndexOf( '.' ) );

const entries = reduce( styleEntries, ( result, dirPath, dirName ) => {
	const jsFiles = getJSFiles( dirPath );
	const jsFileNames = getJSFileNames( dirPath );
	const entryNames = jsFileNames.map(
		filename => `${ dirName }/${ removeExtension( filename ) }`
	);
	return {
		...result,
		...zipObject( entryNames, jsFiles ),
	};
}, { } );

const styleConfig = merge( common, {
	entry: entries,
	output: {
		path: __dirname,
	},
} );

//
// ─── EXPORT CONFIGS ─────────────────────────────────────────────────────────────
//

module.exports = [
	config,
	widgetsConfig,
	styleConfig,
];
