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
				'<%= pkg._resourcepath %>/js/calendar-widget-admin.js',
				'<%= pkg._resourcepath %>/js/events-admin.js',
				'<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.js',
				'<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.js',
				'<%= pkg._resourcepath %>/js/tribe-events-photo-view.js',
				'<%= pkg._resourcepath %>/js/tribe-events-pro.js',
				'<%= pkg._resourcepath %>/js/tribe-events-week.js',
				'<%= pkg._resourcepath %>/js/widget-calendar.js',
				'<%= pkg._resourcepath %>/js/widget-countdown.js'
			]
		}
	}

};
