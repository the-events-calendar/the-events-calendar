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

		'<%= pkg._resourcepath %>/js/events-admin.processed.js',
		'<%= pkg._resourcepath %>/js/tickets.processed.js',
		'<%= pkg._resourcepath %>/js/tickets-attendees.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-ajax-calendar.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-ajax-day.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-ajax-list.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-events-bar.processed.js',
		'<%= pkg._resourcepath %>/js/tribe-settings.processed.js'
	],

	resourcecss: [
		'<%= pkg._resourcepath %>/css/*.min.css'
	]


};
