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

		'<%= pkg._resourcepath %>/calendar-widget-admin.processed.js',
		'<%= pkg._resourcepath %>/events-admin.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-ajax-maps.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-mini-ajax.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-photo-view.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-pro.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-week.processed.js',
		'<%= pkg._resourcepath %>/widget-calendar.processed.js',
		'<%= pkg._resourcepath %>/widget-countdown.processed.js'
	]

};