/**
 *
 * Module: grunt-contrib-watch
 * Documentation: https://npmjs.org/package/grunt-contrib-watch
 * Example:
 *
 css    : {
	files  : ['<%= pkg._resourcepath %>/scss/*.scss'],
	tasks  : ['compass'],
		options: {
		spawn: false
	}
},
 scripts: {
	files  : ['<%= pkg._resourcepath %>/js/*.js'],
	tasks  : ['concat', 'uglify'],
	options: {
		spawn: false
	}
}
 *
 */

module.exports = {

	resourcecss: {
		files: [
			'<%= pkg._resourcepath %>/*.css',
			'<%= pkg._resourcepath %>/!*.min.css'
		],
		tasks: [
			'cssmin:resourcecss'
		],
		options: {
			spawn: false,
			livereload: true
		}
	},
	resourcescripts: {
		files: [
			'<%= pkg._resourcepath %>/calendar-widget-admin.js',
			'<%= pkg._resourcepath %>/events-admin.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-maps.js',
			'<%= pkg._resourcepath %>/tribe-events-mini-ajax.js',
			'<%= pkg._resourcepath %>/tribe-events-photo-view.js',
			'<%= pkg._resourcepath %>/tribe-events-pro.js',
			'<%= pkg._resourcepath %>/tribe-events-week.js',
			'<%= pkg._resourcepath %>/widget-calendar.js',
			'<%= pkg._resourcepath %>/widget-countdown.js'
		],
		tasks: [
			'jshint:resourcescripts',
			'preprocess:resourcescripts',
			'uglify:resourcescripts',
			'clean:resourcescripts'
		],
		options: {
			spawn: false,
			livereload: true
		}
	},
	php: {
		files  : [
			'**/*.php'
		],
		options: {
			spawn     : false,
			livereload: true
		}
	}

};