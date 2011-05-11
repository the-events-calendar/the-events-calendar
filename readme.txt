=== Events Calendar Pro ===

Contributors: Kelsey Damas, Matt Wiebe, Justin Endler, Reid Peifer, Dan Cameron, Aaron Rhodes produced by Shane & Peter, Inc.
Tags: widget, events, simple, tooltips, grid, month, list, calendar, event, venue, eventbrite, registration, tickets, ticketing, eventbright, api, dates, date, plugin, posts, sidebar, template, theme, time, google maps, conference, workshop, concert, meeting, seminar, summit, forum, shortcode
Requires at least: 3.0
Tested up to: 3.1.2
Stable tag: 1.3.1

== Description ==

The Events Calendar Pro plugin enables you to rapidly create and manage events using the post editor.  Features include Google Maps integration as well as default templates such as a calendar grid and event list for streamlined one click installation. Requires WordPress 3.0 and PHP 5.2.

= Events Calendar Pro =

* Manage event details in the Events post type
* Upcoming Events Widget
* Provides full template to complement the 2010 theme out of the box (month and list view)
* Extensive template tags for customization
* MU Compatible
* Google Maps Integration
* Calendar Month view with tooltips
* Includes support for venue, cost, address, start and end time, google maps link
* Support for international addresses, time and languages:
** Czech
** Danish
** Spanish
** French
** Italian
** Dutch
** Polish
** Portuguese
** Russian
** Swedish

= Upcoming Features =

* Recurring events
* Sync with facebook events
* Saved venues
* Global event maps

== Installation ==

= Install =

1. Unzip the `events-calendar-premium.zip` file. 
1. Upload the the `events-calendar-premium` folder (not just the files in it!) to your `wp-content/plugins` folder. If you're using FTP, use 'binary' mode.
1. Update your permalinks to ensure that the event specific rewrite rules take effect.
1. If you have trouble installing, see the [Codex](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation) for more helpful info.

= Activate =

No setup required. Just plug and play!

= Settings = 

There are a growing number of options you can set to make your calendar behave in a specific manner. Simple click The Event Calendar in the settings panel:

* Default View for Events: Select Calendar or Event list as the default view for the events view
* Default Country: Select the default country for creating events
* Embed Google Maps: Turn on embedded Google Maps and define the height and width of the map.
* Date / Time format is now managed via the default WordPress setting

= Requirements =

* PHP 5.1 or above
* WordPress 3.0 or above

== Documentation ==

The built in template can be overridden by files within your template.

= Default vs. Custom Templates =

The Events Calendar plugin comes with default templates for the list view, grid view and single post view, tailored to the 2010 default theme. If you would like to alter them, create a new folder called "events" in your template directory and copy over the following files from within the plugin folder (from the plugin's views/ directory):

* gridview.php
* list.php
* single.php
* events-list-load-widget-display.php
* events.css ( original in the plugin's resources/ directory )

Edit the new files to your hearts content. Please do not edit the one's in the plugin folder as that will cause conflicts when you update the plugin to the latest release.

New in 1.1: You may now also use the `sp_events_template_*` filter to override the default template behavior for .php template files. Eg: if you wanted gridview.php to reside in a folder called events-custom/ and wanted it to be called grid.php:


	add_filter('sd_events_template_gridview', 'my_gridview');
	function my_gridview($template) {
		return get_template_directory() . '/events-custom/grid.php';
	}

For events.css, the filter `sp_events_stylesheet_url` can be used to provide an alternate stylesheet. If your filter returns false, no events stylesheet will be loaded.

= Supported Variables and URLs =

This plugin registers the following rewrite rules, which controls which posts are available in the loop.  The number of posts returned defaults to 10, but is configurable by the $count parameter to sp_get_events().

events/upcoming 
?post_type=sp_events&eventDisplay=upcoming
  
Displays events starting today in ascending date order.
  
events/past
?post_type=sp_events&eventDisplay=past

Displays events that started before today in descending date order.
  
events/2010-01-02
?post_type=sp_events&eventDisplay=bydate&eventDate=2010-01-02

Displays only events that start on Jan 2, 2010.

events/ical
?ical

Provides an iCal file of all Events


= Template Tags =

**sp_get_start_date( $id, $showtime, $dateFormat)**
**sp_get_end_date( $id, $showtime, $dateFormat)**

Date format in order of precedence:
- An format string arg given to event_start_date() or event_end_date()
- WP options
- The constant set in the plugin class

Time format comes from:
- WP options
- Constant in the plugin class

**sp_get_cost( $id )**
**sp_get_venue( $id )**
**sp_get_address( $id )**
**sp_get_city( $id )**
**sp_get_state( $id )**
**sp_get_province( $id )**
**sp_get_zip( $id )**
**sp_get_country( $id )**
**sp_get_phone( $id )**

These functions will return the metadata associated with the event. The ID is optional.

**sp_the_map_link( $id )**
**sp_get_map_link( $id )**

Echos or returns, respectively, an http:// link to google maps for the event's address.  The ID is optional.

**sp_month_year_dropdowns( )**

Prints the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event.

**event_style( $id )**
**get_event_style( $id )**

Echos or returns, respectively, the event class specified in the admin panel.  ID is optional.

**sp_is_new_event_day()**

Called inside of the loop, returns true if the current post's meta_value (EventStartDate) is different than the previous post.   Will always return true for the first event in the loop.

**sp_get_events()**

Call this function in a template to query the events.

Starting in 1.2, this function supports a number of function that may be passed in as an array.
$events_options = array(
	'numResults' => 10, // default is WP's posts_per_page setting.
	'eventCat' => null, // an events category to get events from
	'metaKey' => null, // you can query an additional piece of meta. Useful if, for example, you want all events in a certain country
	'metaValue' => null // the value to query. both metaKey and metaValue are needed.
);
sp_get_events($events_options);

**sp_is_past()**

Returns true if the query is set for past events

**sp_the_embedded_map( $id, $width, $height)**

Returns an embedded google map. Width and height are set through the admin panel unless overridden directly through the function call.

**sp_get_tickets( $id, $width, $height)**

Returns an EventBrite.com embedded ticket sales inline (not WordPress) widget 


== Changelog ==

= 1.3.1 =

* Added support for post titles in previous and next links in the post.

= 1.2.2 =

Features
# Added default values for state, zip, etc
# Google maps are now localized based on WordPress' language setting.
# Added a "No upcoming or previous events" message
# Why show all the countries when you only need two? You can now manage the list of countries
# Added custom page titles for event pages for SEO.
# Added "Post Thumbnail" support to single events and to the popups on the calender display.

Bugs

# Removed Yugoslavia from the country list.
# Fixed a bug that could allow the events plugin to take over the home page on initial install.
# fixed bugs with Google Calendar link bugs
# Upcoming events list now show events that end after now rather than just events that start after now.
# Event cost correctly handles "0" as free.
# Added ability to golle more information in the Calendar Widget.
# Events are now correctly ordered by time in calendar view.
# "Past" events now show up up in chronological order
# All day events now stay on upcoming events page all day
# Issues with browsing multiple pages of events ("page/2/") are now fixed.
# Fixed bug where filters to order event posts by date made it impossible to view past events.
# fixed bug where bulk-editing events deletes date information.

= 1.2 =

Features

# Full events category support! You can now browse by category in both list and calendar views. (If you have a custom template, inspect the differences in ours, because yours won't work with categories out of the box.)
# Customizable /events/ URL slug. In case you want something like http://yoursite.com/concerts
# Translation of URLs enabled through POT file
# Add to Google Calendar button on a single event


Bugfixes

# Restored proper date ordering in admin area
# Translations (where available) should be displaying consistently
# US states now display properly
# Autosave and Preview re-enabled
# No limits to how many events display on a given day

= 1.1.4 =

Features

# abstract plugin directory var so that plugin can be installed directly in the theme.

= 1.1.3 =

Bugfixes

# fixed the single post / list view bug.
# Removed debug mode because of bugs.

= 1.1.2 =

Bugfixes

* translations load correctly
* viewing events in admin now paginates properly

= 1.1.1 =

Bugfixes

* proper version #
* consistent labels for plugin: Events Calendar Pro

= 1.1 =

Features

* Events Calendar widget
* Single event ical import. Uses **sp_get_single_ical_link()** (must be used in the loop)

Bugfixes

* **is_home()** no longer returns true on an Events view
* Debug mode introduced for users seeing wrong counts in the Events admin view. Turn on and ensure that start and end dates are set correctly before turning off. While debug mode is on, Events will not be sorted by start date properly.
* Some instances where the single.php template in your theme's events/ directory would not load properly should now be fixed.

= 1.0 =

Features

* Full port of The Events Calendar 1.6 to Events Calendar Pro 1.0
* Now using custom post types rather than an events category!
* Slick jQuery UI datepicker - no more fiddling with 3 dropdowns!
* Lots of code refactoring for a better experience
* Ability to set Events-specific categories