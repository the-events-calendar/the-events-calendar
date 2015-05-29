/**
 *
 * Module: grunt-contrib-uglify
 * Documentation: https://npmjs.org/package/grunt-contrib-uglify
 * Example:
 *
 	my_target: {
      files: {
        'dest/output.min.js': ['src/input1.js', 'src/input2.js']
      }
    }
 *
 */

module.exports = {

	resourcescripts: {

		files: {
			'<%= pkg._resourcepath %>/js/calendar-widget-admin.min.js' : '<%= pkg._resourcepath %>/js/calendar-widget-admin.processed.js',
			'<%= pkg._resourcepath %>/js/events-admin.min.js' : '<%= pkg._resourcepath %>/js/events-admin.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-photo-view.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-photo-view.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-pro.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-pro.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-week.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-week.processed.js',
			'<%= pkg._resourcepath %>/js/widget-calendar.min.js' : '<%= pkg._resourcepath %>/js/widget-calendar.processed.js',
			'<%= pkg._resourcepath %>/js/widget-countdown.min.js' : '<%= pkg._resourcepath %>/js/widget-countdown.processed.js'
		}
	}

};
