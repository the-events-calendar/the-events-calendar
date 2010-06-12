=== Events Calendar Premium ===

Contributors: Kelsey Damas, Matt Wiebe, Justin Endler, Reid Peifer, Dan Cameron, Aaron Rhodes produced by Shane & Peter, Inc.
Tags: widget, events, simple, tooltips, grid, month, list, calendar, event, venue, eventbrite, registration, tickets, ticketing, eventbright, api, dates, date, plugin, posts, sidebar, template, theme, time, google maps, conference, workshop, concert, meeting, seminar, summit, forum, shortcode
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.6

== Description ==

The Events Calendar Premium plugin enables you to rapidly create and manage events using the post editor.  Features include Google Maps integration as well as default templates such as a calendar grid and event list for streamlined one click installation.

= Events Calendar Premium =

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

* Calendar view widget
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

= Supported Variables and URLs =

This plugin registers the following rewrite rules, which controls which posts are available in the loop.  The number of posts returned defaults to 10, but is configurable by the $count parameter to get_events().

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

**the_event_start_date( $id, $showtime, $dateFormat)**
**the_event_end_date( $id, $showtime, $dateFormat)**

Date format in order of precedence:
- An format string arg given to event_start_date() or event_end_date()
- WP options
- The constant set in the plugin class

Time format comes from:
- WP options
- Constant in the plugin class

**the_event_cost( $id )**
**the_event_venue( $id )**
**the_event_address( $id )**
**the_event_city( $id )**
**the_event_state( $id )**
**the_event_province( $id )**
**the_event_zip( $id )**
**the_event_country( $id )**
**the_event_phone( $id )**

These functions will return the metadata associated with the event. The ID is optional.

**event_google_map_link( $id )**
**get_event_google_map_link( $id )**

Echos or returns, respectively, an http:// link to google maps for the event's address.  The ID is optional.

**get_jump_to_date_calendar( )**

Returns a string containing a javascript date calendar.

**event_style( $id )**
**get_event_style( $id )**

Echos or returns, respectively, the event class specified in the admin panel.  ID is optional.

**is_new_event_day()**

Called inside of the loop, returns true if the current post's meta_value (EventStartDate) is different than the previous post.   Will always return true for the first event in the loop.

**get_events( $count )**

Call this function in a template to query the events. $count is optional.

**events_displaying_past()**

Returns true if the query is set for past events

**event_google_map_embed( $id, $width, $height)**

Returns an embedded google map. Width and height are set through the admin panel unless overridden directly through the function call.

**the_event_tickets( $id, $width, $height)**

Returns an EventBrite.com embedded ticket sales inline (not WordPress) widget 


== Screenshots ==

1. Grid View Template
1. List View Template
1. Single Post Template
1. Settings Panel
1. Post (Event) Editor
1. Widget Admin
1. Unstyled Widget

== FAQ ==


== Changelog ==

= 1.0 =

Features

* Full port of The Events Calendar 1.6 to Events Calendar Premium 1.0
* Now using custom post types rather than an events category!
* Slick jQuery UI datepicker - no more fiddling with 3 dropdowns!
* Lots of code refactoring for a better experience
* Ability to set Events-specific categories