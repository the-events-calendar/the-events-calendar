const eslintConfig = require( '@wordpress/scripts/config/.eslintrc.js' );

module.exports = {
	...eslintConfig,
	overrides: [
		...eslintConfig.overrides,
	]
};
