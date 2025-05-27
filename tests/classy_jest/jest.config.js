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
					esModuleInterop: true,
					jsx: 'react-jsx',
					allowUmdGlobalAccess: true,
					sourceMap: true,
					allowJs: true,
				},
			},
		],
	},
	preset: 'ts-jest',
	moduleFileExtensions: [ 'ts', 'tsx', 'js', 'jsx' ],
};
