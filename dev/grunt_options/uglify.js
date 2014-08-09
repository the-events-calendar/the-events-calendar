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
			'<%= pkg._resourcepath %>/calendar-widget-admin.min.js' : '<%= pkg._resourcepath %>/calendar-widget-admin.processed.js',
			'<%= pkg._resourcepath %>/events-admin.min.js' : '<%= pkg._resourcepath %>/events-admin.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-maps.min.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-maps.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-mini-ajax.min.js' : '<%= pkg._resourcepath %>/tribe-events-mini-ajax.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-photo-view.min.js' : '<%= pkg._resourcepath %>/tribe-events-photo-view.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-pro.min.js' : '<%= pkg._resourcepath %>/tribe-events-pro.processed.js',
			'<%= pkg._resourcepath %>/tribe-events-week.min.js' : '<%= pkg._resourcepath %>/tribe-events-week.processed.js',
			'<%= pkg._resourcepath %>/widget-calendar.min.js' : '<%= pkg._resourcepath %>/widget-calendar.processed.js',
			'<%= pkg._resourcepath %>/widget-countdown.min.js' : '<%= pkg._resourcepath %>/widget-countdown.processed.js'
		}
	}

};