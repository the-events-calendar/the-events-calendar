/**
 * External dependencies
 */
const { resolve } = require( 'path' );
const { reduce, zipObject } = require( 'lodash' );
const merge = require( 'webpack-merge' );
const common = require( '@the-events-calendar/product-taskmaster/webpack/common/webpack.config' );
const { getDirectoryNames, getDirectories } = require( '@the-events-calendar/product-taskmaster/webpack/utils/directories' );
const { getJSFileNames, getJSFiles } = require( '@the-events-calendar/product-taskmaster/webpack/utils/files' );
const { generateEntries } = require( '@the-events-calendar/product-taskmaster/webpack/entry/tribe' );

const directoryNames = getDirectoryNames( resolve( __dirname, './src/modules' ) );
const PLUGIN_SCOPE = 'events';

//
// ────────────────────────────────────────────────────────────────────────────────────── I ──────────
//   :::::: G E N E R A T E   E V E N T S   P L U G I N : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────────────────────
//

const config = merge( common, {
	entry: generateEntries( __dirname, directoryNames ),
	output: {
		path: __dirname,
		library: [ 'tribe', PLUGIN_SCOPE, '[name]' ],
	},
} );

//
// ──────────────────────────────────────────────────────────────────────────────────────────── II ──────────
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
	styleConfig,
];
