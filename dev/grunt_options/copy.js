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
				'!**/.git/**',
				'!**/.editorconfig',
				'!**/phpunit.xml',
				'!**/codeception*.yml',
				'!**/composer.json',
				'!**/composer.lock',
				'!**/vendor/bacon/**',
				'!**/vendor/badcow/**',
				'!**/vendor/bin/**',
				'!**/vendor/codeception/**',
				'!**/vendor/composer/**',
				'!**/vendor/doctrine/**',
				'!**/vendor/facebook/**',
				'!**/vendor/guzzlehttp/**',
				'!**/vendor/isotope/index.html',
				'!**/vendor/isotope/_includes/**',
				'!**/vendor/isotope/_layouts/**',
				'!**/vendor/isotope/_posts/**',
				'!**/vendor/isotope/js/**',
				'!**/vendor/lucatume/**',
				'!**/vendor/phpdocumentor/**',
				'!**/vendor/phpspec/**',
				'!**/vendor/phpunit/**',
				'!**/vendor/psr/**',
				'!**/vendor/sebastian/**',
				'!**/vendor/symfony/**',
				'!**/vendor/autoload.php'
			],
			dest: '<%= pkg._zipfoldername %>/'
		}]
	}

};
