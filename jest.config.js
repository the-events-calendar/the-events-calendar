var sharedConfig = require( '@the-events-calendar/product-taskmaster/config/jest.config.js' );

module.exports = {
	...sharedConfig,
	displayName: 'common',
	testMatch: [
		'**/data/**/__tests__/**/*.js',
	],
};
