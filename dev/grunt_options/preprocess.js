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
			'<%= pkg._resourcepath %>/js/events-admin.processed.js' : '<%= pkg._resourcepath %>/js/events-admin.js',
			'<%= pkg._resourcepath %>/js/tickets.processed.js' : '<%= pkg._resourcepath %>/js/tickets.js',
			'<%= pkg._resourcepath %>/js/tickets-attendees.processed.js' : '<%= pkg._resourcepath %>/js/tickets-attendees.js',
			'<%= pkg._resourcepath %>/js/tribe-events.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-day.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-day.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-list.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-list.js',
			'<%= pkg._resourcepath %>/js/tribe-events-bar.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-bar.js',
			'<%= pkg._resourcepath %>/js/tribe-settings.processed.js' : '<%= pkg._resourcepath %>/js/tribe-settings.js'
		}
	}

};
