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
			'<%= pkg._resourcepath %>/js/calendar-widget-admin.processed.js' : '<%= pkg._resourcepath %>/js/calendar-widget-admin.js',
			'<%= pkg._resourcepath %>/js/events-admin.processed.js' : '<%= pkg._resourcepath %>/js/events-admin.js',
			'<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.js',
			'<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.js',
			'<%= pkg._resourcepath %>/js/tribe-events-photo-view.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-photo-view.js',
			'<%= pkg._resourcepath %>/js/tribe-events-pro.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-pro.js',
			'<%= pkg._resourcepath %>/js/tribe-events-week.processed.js' : '<%= pkg._resourcepath %>/js/tribe-events-week.js',
			'<%= pkg._resourcepath %>/js/widget-calendar.processed.js' : '<%= pkg._resourcepath %>/js/widget-calendar.js',
			'<%= pkg._resourcepath %>/js/widget-countdown.processed.js' : '<%= pkg._resourcepath %>/js/widget-countdown.js'
		}
	}

};
