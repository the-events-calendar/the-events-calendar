const {defaults: tsjPreset} = require('ts-jest/presets');

module.exports = {
	verbose: true,
	setupFiles: [__dirname + '/jest.setup.js'],
	testEnvironment: 'jest-environment-jsdom-global',
	testMatch: ['**/*.spec.ts', '**/*.spec.tsx'],
	transform: {
		'^.+\.tsx?$': [
			'ts-jest', {
				tsconfig: {
					esModuleInterop: true,
					jsx: 'react',
				},
			},
		],
	},
	preset: 'ts-jest',
	moduleFileExtensions: [
		'ts',
		'tsx',
		'js',
		'jsx',
	],
};
