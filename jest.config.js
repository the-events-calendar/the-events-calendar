var sharedConfig = require( '@the-events-calendar/product-taskmaster/config/jest.config.js' );
var pkg = require( './package.json' );

module.exports = {
	...sharedConfig,
	displayName: 'events',
	testMatch: pkg._filePath.jest.map( ( path ) => `<rootDir>/${ path }` ),
	"modulePathIgnorePatterns": [
        "<rootDir>/common"
	]
};
