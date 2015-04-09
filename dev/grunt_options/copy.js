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
				src: ['**',
				      '!**/dev/**',
				      '!**/tests/**',
				      '!**/*.sh',
				      '!**/phpunit.xml',
				      '!**/codeception.yml',
				      '!**/composer.json',
				      '!**/composer.lock',
				      '!**/vendor/badcow/**',
				      '!**/vendor/bin/**',
				      '!**/vendor/codeception/**',
				      '!**/vendor/composer/**',
				      '!**/vendor/facebook/**',
				      '!**/vendor/guzzlehttp/**',
				      '!**/vendor/lucatume/**',
				      '!**/vendor/ocramius/**',
				      '!**/vendor/phpunit/**',
				      '!**/vendor/sebastian/**',
				      '!**/vendor/symfony/**',
				      '!**/vendor/autoload.php',
				      '!**/vendor/jquery-placeholder/demo.html',
				      '!**/vendor/jquery-resize/docs/**',
				      '!**/vendor/jquery-resize/examples/**',
				      '!**/vendor/jquery-resize/shared/**',
				      '!**/vendor/jquery-resize/unit/**'
				],
				dest: '<%= pkg._zipfoldername %>/'
			}]
	}

};