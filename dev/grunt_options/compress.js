/**
 *
 * Module: grunt-contrib-compress
 * Documentation: https://npmjs.org/package/grunt-contrib-compress
 * Example:
 *
 dist: {
		options: {
			mode   : 'zip',
			level  : 9,
			archive: '<%= pkg.name %>.<%= pkg.version %>.zip'
		},
		files  : [
			{
				expand: true,
				src   : ['<%= pkg.name %>/**']
			}
		]
	}
 *
 */

module.exports = {

	dist: {
		options: {
			mode   : 'zip',
			level  : 9,
			archive: '<%= pkg._zipname %>.<%= pkg.version %>.zip'
		},
		files  : [
			{
				expand: true,
				src   : ['<%= pkg._zipfoldername %>/**']
			}
		]
	}

};