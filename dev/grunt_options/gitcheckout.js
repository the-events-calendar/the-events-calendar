/**
 *
 * Module: grunt-git
 * Documentation: https://www.npmjs.org/package/grunt-git
 *
 *
 */

module.exports = {

	options: {
		verbose:true
	},

	dist: {
		options: {
			branch:'master'
		}
	},

	dev: {
		options: {
			branch:'release/119'
		}
	}
};