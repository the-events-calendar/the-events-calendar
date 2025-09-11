const path = require( 'path' );
const commonDir = '../../common/src/resources/packages';
const eventsDir = '../../src/resources/packages';

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
					allowImportingTsExtensions: true,
					allowJs: true,
					allowSyntheticDefaultImports: true,
					allowUmdGlobalAccess: true,
					alwaysStrict: true,
					baseUrl: '.',
					checkJs: true,
					esModuleInterop: true,
					jsx: 'react-jsx',
					moduleResolution: 'node10',
					noEmit: true,
					noImplicitReturns: true,
					sourceMap: true,
					target: 'esnext',
					paths: {
						'@tec/common/*': [ `${commonDir}/*` ],
						'@tec/events/*': [ `${eventsDir}/*` ],
					},
				},
			},
		],
	},
	transformIgnorePatterns: [ '/node_modules/(?!client-zip|@wordpress/.*)' ],
	preset: 'ts-jest',
	moduleFileExtensions: [ 'ts', 'tsx', 'js', 'jsx' ],
	snapshotSerializers: [ '@emotion/jest/serializer' ],
	moduleNameMapper: {
		'@tec/common/(.*)$': `<rootDir>/${commonDir}/$1`,
		'@tec/events/(.*)$': `<rootDir>/${eventsDir}/$1`,
	},
};
