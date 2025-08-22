const { defaults: tsjPreset } = require( 'ts-jest/presets' );
const path = require( 'path' );

// Find the project root (where package.json is located).
const projectRoot = path.resolve( __dirname, '../..' );

module.exports = {
	verbose: true,
	setupFiles: [ __dirname + '/jest.setup.js' ],
	testEnvironment: 'jest-environment-jsdom-global',
	testMatch: [ '**/*.spec.ts', '**/*.spec.tsx' ],
	resolver: __dirname + '/jest-resolver.js',
	transform: {
		'^.+.tsx?$': [
			'ts-jest',
			{
				tsconfig: {
					allowJs: true,
					checkJs: true,
					target: 'esnext',
					allowSyntheticDefaultImports: true,
					allowUmdGlobalAccess: true,
					esModuleInterop: true,
					jsx: 'react-jsx',
					sourceMap: true,
				},
			},
		],
		'^.+\\.js$': 'babel-jest',
	},
	transformIgnorePatterns: [ '/node_modules/(?!client-zip|@wordpress/.*)' ],
	preset: 'ts-jest',
	moduleFileExtensions: [ 'ts', 'tsx', 'js', 'jsx' ],
	snapshotSerializers: [ '@emotion/jest/serializer' ],
	// Load modules only from TEC, override default resolution that could lead Common loading from its own `node_modules`.
	moduleDirectories: [ path.resolve( __dirname, '../../node_modules' ) ],
	moduleNameMapper: {
		'@tec/common/(.*)$': projectRoot + '/common/src/resources/packages/$1',
		'@tec/common/classy/(.*)$': projectRoot + '/common/src/resources/packages/classy/$1',
	},
};
