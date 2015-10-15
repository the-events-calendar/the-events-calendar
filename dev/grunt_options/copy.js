/**
 *
 * Module: grunt-contrib-copy
 * Documentation: https://npmjs.org/package/grunt-contrib-copy
 * Example:
 *
 main: {
		files: [
		  // includes files within path
		  {expand: true, src: ['path/*'], dest: 'dest/', filter: 'isFile'},

		  // includes files within path and its sub-directories
		  {expand: true, src: ['path/**'], dest: 'dest/'},

		  // makes all src relative to cwd
		  {expand: true, cwd: 'path/', src: ['**'], dest: 'dest/'},

		  // flattens results to a single level
		  {expand: true, flatten: true, src: ['path/**'], dest: 'dest/', filter: 'isFile'}
		]
  	}
 *
 */

module.exports = {

	dist: {
		files: [{
			expand: true,
			src: ['*',
				'**/lang/**',
				'**/src/**',
				'!**/dev/**',
				'!**/tests/**',
				'!**/phpunit.xml',
				'!**/tests.md',
				'!**/codeception*.yml',
				'!**/composer.json',
				'!**/composer.lock'
			],
			dest: '<%= pkg._zipfoldername %>/'
		}]
	}

};
