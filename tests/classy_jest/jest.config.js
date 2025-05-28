const { defaults: tsjPreset } = require( 'ts-jest/presets' );

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
	},
	preset: 'ts-jest',
	moduleFileExtensions: [ 'ts', 'tsx', 'js', 'jsx' ],
	snapshotSerializers: [ '@emotion/jest/serializer' ],
};
