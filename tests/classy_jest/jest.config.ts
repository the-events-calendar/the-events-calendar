import type { Config } from 'jest';

const config: Config = {
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
					target: 'ES2022',
					lib: [ 'ES2022', 'DOM' ],
					allowSyntheticDefaultImports: true,
					allowUmdGlobalAccess: true,
					esModuleInterop: true,
					jsx: 'react-jsx',
					sourceMap: true,
				},
			},
		],
	},
	transformIgnorePatterns: [ '/node_modules/(?!client-zip|@wordpress/.*)' ],
	preset: 'ts-jest',
	moduleFileExtensions: [ 'ts', 'tsx', 'js', 'jsx' ],
	snapshotSerializers: [ '@emotion/jest/serializer' ],
	moduleNameMapper: {
		// Map @tec/common to the common directory.
		'@tec/common/(.*)$': '<rootDir>/../../common/src/resources/packages/$1',
		// Map @tec/events to the events directory.
		'@tec/events/(.*)$': '<rootDir>/../../src/resources/packages/$1',
	},
};

export default config;
