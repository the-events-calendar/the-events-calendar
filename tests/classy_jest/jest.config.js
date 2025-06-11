const { defaults: tsjPreset } = require( 'ts-jest/presets' );

module.exports = {
	verbose: true,
	setupFiles: [ __dirname + '/jest.setup.ts' ],
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
	moduleNameMapper: {
		'@tec/common/(.*)$': '<rootDir>/../../common/src/resources/packages/$1',
		'@tec/common/classy/(.*)$': '<rootDir>/../../common/src/resources/packages/classy/$1',
	},
};
