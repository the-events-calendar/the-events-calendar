var pkg = require('./package.json');

module.exports = {
		verbose: true,
		setupFiles: [
				'<rootDir>/jest.setup.js',
		],
		moduleNameMapper: {
				'\\.(css|pcss)$': 'identity-obj-proxy',
				'\\.(svg)$': '<rootDir>/__mocks__/icons.js',
		},
		testEnvironment: 'jest-environment-jsdom-global',
		displayName: 'events',
		testMatch: pkg._filePath.jest.map((path) => `<rootDir>/${path}`),
		'modulePathIgnorePatterns': [
				'<rootDir>/common',
		],
		transform: {
				'^.+\.tsx?$': ['ts-jest', {}],
		},
};
