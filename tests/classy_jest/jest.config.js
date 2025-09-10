const { defaults: tsjPreset } = require( 'ts-jest/presets' );
const path = require( 'path' );

module.exports = {
	verbose: true,
	setupFiles: [ `${ __dirname }/jest.setup.ts` ],
	testEnvironment: 'jest-environment-jsdom-global',
	testMatch: [ '**/*.spec.ts', '**/*.spec.tsx' ],
	resolver: `${ __dirname }/jest-resolver.js`,
	transform: {
		'^.+\\.tsx?$': [
			'ts-jest',
			{
				tsconfig: {
					allowJs: true,
					baseUrl: '.',
					checkJs: true,
					target: 'esnext',
					allowSyntheticDefaultImports: true,
					allowUmdGlobalAccess: true,
					esModuleInterop: true,
					jsx: 'react-jsx',
					sourceMap: true,
					paths: {
						'@tec/common/*': [ '../../common/src/resources/packages/*' ],
						'@tec/events/*': [ '../../src/resources/packages/*' ],
					},
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
		'@tec/common/(.*)$': '<rootDir>/../../common/src/resources/packages/$1',
	},
};
