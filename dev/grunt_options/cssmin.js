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

		expand: true,
		src   : ['<%= pkg._resourcepath %>/css/*.css', '<%= pkg._resourcepath %>/css/!*.min.css'],
		ext   : '.min.css'
	}

};
