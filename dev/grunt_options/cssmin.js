/**
 *
 * Module: grunt-contrib-cssmin
 * Documentation: https://npmjs.org/package/grunt-contrib-cssmin
 * Example:
 *

 minify: {
    expand: true,
    cwd: 'release/css/',
    src: ['*.css', '!*.min.css'],
    dest: 'release/css/',
    ext: '.min.css'
  }

 *
 */

module.exports = {

	resourcecss: {

		options: {
			banner: '/*\n' +
				' * built on <%= grunt.template.today("dd-mm-yyyy") %>\n' +
				' */\n'
		},

		expand: true,
		src   : ['<%= pkg._resourcepath %>/*.css', '<%= pkg._resourcepath %>/!*.min.css'],
		ext   : '.min.css'
	}

};