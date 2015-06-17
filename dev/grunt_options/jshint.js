/**
 *
 * Module: grunt-contrib-jshint
 * Documentation: https://npmjs.org/package/grunt-contrib-jshint
 * Example:
 *
 options: {
		  curly: true,
		  eqeqeq: true,
		  eqnull: true,
		  browser: true,
		  globals: {
			jQuery: true
		  },
		},
 uses_defaults: ['dir1/*.js', 'dir2/*.js'],
 with_overrides: {
		options: {
			curly: false,
				undef: true,
		},
		files: {
			src: ['dir3/*.js', 'dir4/*.js']
		},
	}
 *
 */

module.exports = {

	resourcescripts: {

		options: {
			curly         : true,
			eqeqeq        : true,
			eqnull        : true,
			browser       : true,
			unused        : true,
			force         : true,
			globals       : {
				jQuery: true
			},
			reporter      : require( 'jshint-html-reporter' ),
			reporterOutput: 'dev/jshint-report.html'
		},
		files  : {
			src: [
				'<%= pkg._resourcepath %>/js/events-admin.js',
				'<%= pkg._resourcepath %>/js/tickets.js',
				'<%= pkg._resourcepath %>/js/tickets-attendees.js',
				'<%= pkg._resourcepath %>/js/tribe-events.js',
				'<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.js',
				'<%= pkg._resourcepath %>/js/tribe-events-ajax-day.js',
				'<%= pkg._resourcepath %>/js/tribe-events-ajax-list.js',
				'<%= pkg._resourcepath %>/js/tribe-events-bar.js',
				'<%= pkg._resourcepath %>/js/tribe-settings.js'
			]
		}
	}

};
