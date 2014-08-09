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
		options: {
			banner: '/*\n' +
				' * built on <%= grunt.template.today("dd-mm-yyyy") %>\n' +
				' */\n'
		},
		files: {
			'<%= pkg._resourcepath %>/events-admin.min.js' : '<%= pkg._resourcepath %>/events-admin.processed.js',
			'<%= pkg._resourcepath %>/tickets.min.js' : '<%= pkg._resourcepath %>/tickets.processed.js',
			'<%= pkg._resourcepath %>/tickets-attendees.min.js' : '<%= pkg._resourcepath %>/tickets-attendees.processed.js',
			'<%= pkg._resourcepath %>/tribe-events.min.js' : '<%= pkg._resourcepath %>/tribe-events.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-calendar.min.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-calendar.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-day.min.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-day.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-list.min.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-list.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-bar.min.js' : '<%= pkg._resourcepath %>/tribe-events-bar.processed.js',
			'<%= pkg._resourcepath %>/tribe-settings.min.js' : '<%= pkg._resourcepath %>/tribe-settings.processed.js'
		}
	}

};