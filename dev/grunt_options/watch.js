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
			'<%= pkg._resourcepath %>/events-admin.js',
			'<%= pkg._resourcepath %>/tickets.js',
			'<%= pkg._resourcepath %>/tickets-attendees.js',
			'<%= pkg._resourcepath %>/tribe-events.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-calendar.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-day.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-list.js',
			'<%= pkg._resourcepath %>/tribe-events-bar.js',
			'<%= pkg._resourcepath %>/tribe-settings.js'
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