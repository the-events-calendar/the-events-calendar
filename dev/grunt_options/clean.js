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

		'<%= pkg._resourcepath %>/events-admin.processed.js',
		'<%= pkg._resourcepath %>/tickets.processed.js',
		'<%= pkg._resourcepath %>/tickets-attendees.processed.js',
		'<%= pkg._resourcepath %>/tribe-events.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-ajax-calendar.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-ajax-day.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-ajax-list.processed.js',
		'<%= pkg._resourcepath %>/tribe-events-bar.processed.js',
		'<%= pkg._resourcepath %>/tribe-settings.processed.js'
	],

	resourcecss: [
		'<%= pkg._resourcepath %>/*.min.css'
	]


};