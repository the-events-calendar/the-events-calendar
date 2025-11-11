var pkg = require( './package.json' );

module.exports = {
	verbose: true,
	setupFiles: [ '<rootDir>/jest.setup.js' ],
	displayName: 'events',
	testEnvironment: 'jest-environment-jsdom-global',
	testMatch: pkg._filePath.jest.map( ( path ) => `<rootDir>/${ path }` ),
	modulePathIgnorePatterns: [ '<rootDir>/common' ],
	moduleNameMapper: {
		'\\.(css|pcss)$': 'identity-obj-proxy',
		'\\.(svg)$': '<rootDir>/__mocks__/icons.js',
	},
	// Modules that should not be transformed by Jest.
	transformIgnorePatterns: [ '/node_modules/(?!(date-fns|cheerio)/)' ],
	// Explicitly specify we want to use Babel for transformation
	transform: {
		'^.+\\.(js|jsx|ts|tsx)$': 'babel-jest',
	},
};
