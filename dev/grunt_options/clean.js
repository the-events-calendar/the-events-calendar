/**
 *
 * Module: grunt-contrib-clean
 * Documentation: https://npmjs.org/package/grunt-contrib-clean
 * Example:
 *
 build: ["path/to/dir/one", "path/to/dir/two"],
 release: ["path/to/another/dir/one", "path/to/another/dir/two"]
 *
 */

module.exports = {

	dist: [
		'<%= pkg._zipfoldername %>/**'
	],

	resourcescripts: [

		'<%= pkg._resourcepath %>/js/calendar-widget-admin.processed.js',
		'<%= pkg._resourcepath %>/js/events-admin.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-ajax-maps.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-mini-ajax.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-photo-view.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-pro.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-week.processed.js',
		'<%= pkg._resourcepath %>/js/widget-calendar.processed.js',
		'<%= pkg._resourcepath %>/js/widget-countdown.processed.js'
	],

	resourcecss: [
		'<%= pkg._resourcepath %>/css/*.min.css'
	]

};
