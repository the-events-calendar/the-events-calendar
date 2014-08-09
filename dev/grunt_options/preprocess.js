/**
 *
 * Module: grunt-preprocess
 * Documentation: https://npmjs.org/package/grunt-preprocess
 * Example:
 *
 options: {
    context : {
      DEBUG: true
    }
  },
 html : {
    src : 'test/test.html',
    dest : 'test/test.processed.html'
  },
 multifile : {
    files : {
      'test/test.processed.html' : 'test/test.html',
      'test/test.processed.js'   : 'test/test.js'
    }
  },
 inline : {
    src : [ 'processed/*.js' ],
	options: {
		inline : true,
			context : {
			DEBUG: false
		}
	}
	},
	js : {
		src : 'test/test.js',
			dest : 'test/test.processed.js'
	}
 *
 */

module.exports = {

	options: {
		context : {}
	},
	resourcescripts : {
		files : {
			'<%= pkg._resourcepath %>/events-admin.processed.js' : '<%= pkg._resourcepath %>/events-admin.js',
			'<%= pkg._resourcepath %>/tickets.processed.js' : '<%= pkg._resourcepath %>/tickets.js',
			'<%= pkg._resourcepath %>/tickets-attendees.processed.js' : '<%= pkg._resourcepath %>/tickets-attendees.js',
			'<%= pkg._resourcepath %>/tribe-events.processed.js' : '<%= pkg._resourcepath %>/tribe-events.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-calendar.processed.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-calendar.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-day.processed.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-day.js',
			'<%= pkg._resourcepath %>/tribe-events-ajax-list.processed.js' : '<%= pkg._resourcepath %>/tribe-events-ajax-list.js',
			'<%= pkg._resourcepath %>/tribe-events-bar.processed.js' : '<%= pkg._resourcepath %>/tribe-events-bar.js',
			'<%= pkg._resourcepath %>/tribe-settings.processed.js' : '<%= pkg._resourcepath %>/tribe-settings.js'
		}
	}

};