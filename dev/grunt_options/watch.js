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
			'<%= pkg._resourcepath %>/css/*.css',
			'<%= pkg._resourcepath %>/css/!*.min.css'
		],
		tasks: [
			'clean:resourcecss',
			'cssmin:resourcecss'
		],
		options: {
			spawn: false,
			livereload: true
		}
	},
	resourcescripts: {
		files: [
			'<%= pkg._resourcepath %>/js/calendar-widget-admin.js',
			'<%= pkg._resourcepath %>/js/events-admin.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.js',
			'<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.js',
			'<%= pkg._resourcepath %>/js/tribe-events-photo-view.js',
			'<%= pkg._resourcepath %>/js/tribe-events-pro.js',
			'<%= pkg._resourcepath %>/js/tribe-events-week.js',
			'<%= pkg._resourcepath %>/js/widget-calendar.js',
			'<%= pkg._resourcepath %>/js/widget-countdown.js'
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
