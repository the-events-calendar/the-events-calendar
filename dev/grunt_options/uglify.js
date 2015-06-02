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
			'<%= pkg._resourcepath %>/js/events-admin.min.js' : '<%= pkg._resourcepath %>/js/events-admin.processed.js',
			'<%= pkg._resourcepath %>/js/tickets.min.js' : '<%= pkg._resourcepath %>/js/tickets.processed.js',
			'<%= pkg._resourcepath %>/js/tickets-attendees.min.js' : '<%= pkg._resourcepath %>/js/tickets-attendees.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events.min.js' : '<%= pkg._resourcepath %>/js/tribe-events.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-day.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-day.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-list.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-list.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-events-bar.min.js' : '<%= pkg._resourcepath %>/js/tribe-events-bar.processed.js',
			'<%= pkg._resourcepath %>/js/tribe-settings.min.js' : '<%= pkg._resourcepath %>/js/tribe-settings.processed.js'
		}
	}

};
