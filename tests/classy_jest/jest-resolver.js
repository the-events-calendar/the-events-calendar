// Kudos: https://github.com/Automattic/jetpack/blob/a27e589fab60d018619922be9c9315c08d591fa8/tools/js-tools/jest/jest-resolver.js

// Some packages assume that a "browser" environment is esm or otherwise break in node.
// List them here and the resolver will adjust the conditions to resolve them as "node" instead.
// cf. https://github.com/microsoft/accessibility-insights-web/pull/5421#issuecomment-1109168149
const badBrowserPackages = new Set( [
	// v3 is still supposed to be commonjs-compatible. https://github.com/ai/nanoid/issues/462
	'nanoid',
	// https://github.com/LeaVerou/parsel/issues/79
	'parsel-js',
] );

module.exports = ( path, options ) => {
	const basedir = options.basedir;
	const conditions = options.conditions ? new Set( options.conditions ) : options.conditions;

	// Adjust conditions for certain packages that assume "browser" is esm.
	const pkg = path
		.split( '/' )
		.slice( 0, path.startsWith( '@' ) ? 2 : 1 )
		.join( '/' );
	if ( conditions && conditions.has( 'browser' ) && badBrowserPackages.has( pkg ) ) {
		conditions.delete( 'browser' );
		conditions.add( 'node' );
	}

	// If basedir is from common/src, add node_modules to moduleDirectory
	if ( basedir && basedir.includes( 'common/src' ) ) {
		const nodePath = require( 'path' );
		const nodeModulesPath = nodePath.resolve( __dirname, '../../node_modules' );
		options.moduleDirectory = options.moduleDirectory || [];
		if ( !Array.isArray( options.moduleDirectory ) ) {
			options.moduleDirectory = [ options.moduleDirectory ];
		}
		options.moduleDirectory.push( nodeModulesPath );
	}

	return options.defaultResolver( path, {
		...options,
		basedir,
		conditions,
	} );
};
