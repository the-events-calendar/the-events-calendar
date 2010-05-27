=== The Events Calendar ===

Contributors: Kelsey Damas, Matt Wiebe, Justin Endler, Reid Peifer, Dan Cameron, Aaron Rhodes produced by Shane & Peter, Inc.
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10750983
Tags: widget, events, simple, tooltips, grid, month, list, calendar, event, venue, eventbrite, registration, tickets, ticketing, eventbright, api, dates, date, plugin, posts, sidebar, template, theme, time, google maps, conference, workshop, concert, meeting, seminar, summit, forum, shortcode
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 1.6

== Description ==

The Events Calendar plugin enables you to rapidly create and manage events using the post editor.  Features include Google Maps integration as well as default templates such as a calendar grid and event list for streamlined one click installation.

Looking to track attendees, sell tickets and more? Go download the Eventbrite for The Events Calendar plugin (http://wordpress.org/extend/plugins/eventbrite-for-the-events-calendar/). Eventbrite is a free service that provides the full power of a conference ticketing system. This plugin upgrades The Events Calendar with all the basic Eventbrite controls without ever leaving the wordpress post editor. Don't have an Eventbrite account? No problem, use the following link to set one up: http://www.eventbrite.com/r/simpleevents.

= The Events Calendar =

* Manage event details right from your post editor
* Upcoming Events Widget
* Provides full template out of the box (month and list view)
* Extensive template tags for customization
* MU Compatible
* Google Maps Integration
* Posts are automatically moved to the top of the loop on the day of the event
* Calendar Month view with tooltips
* Includes support for venue, cost, address, start and end time, google maps link
* Optional Ticketing With Eventbrite Integration - http://www.eventbrite.com/ - though the Eventbrite for The Events Calendar plugin (http://wordpress.org/extend/plugins/eventbrite-for-the-events-calendar/).
* Support for international addresses, time and languages:
** Swedish
** French
** Italian

= Upcoming Features =

* Option to disable re-posting of event
* Improved international features (calendar start day)
* Option to exclude events from main loop
* More bug hunting and support (huff puff)
* Improved error checking and reporting
* Calendar view widget
* Reoccuring events
* Sync with facebook events
* Saved venues
* Global event maps
* Dynamic categories (rather than requiring the use of event)
* Event subcategories

Please visit the forum for feature suggestions: http://wordpress.org/tags/the-events-calendar/

This plugin is actively supported and we will do our best to help you. In return we simply as 3 things:

1. Help Out. If you see a question on the forum you can help with or have a great idea and want to code it up and submit a patch, that would be just plain awesome and we will shower your with praise. Might even be a good way to get to know us and lead to some paid work if you freelance.
1. Donate - if this is generating enough revenue to support our time it makes all the difference in the world
1. If you make a new account with Eventbrite, please use our referral code. It helps, believe me: http://www.eventbrite.com/r/simpleevents


== Installation ==

= Install =

1. Unzip the `the-events-calendar.zip` file. 
1. Upload the the `the-events-calendar` folder (not just the files in it!) to your `wp-contents/plugins` folder. If you're using FTP, use 'binary' mode.
1. Update your permalinks to ensure that the event specific rewrite rules take effect.

= Activate =

No setup required. Just plug and play!

= Settings = 

There are a growing number of options you can set to make your calendar behave in a specific manner. Simple click The Event Calendar in the settings panel:

* Donation: Remove our plea for your support
* Default View for Events: Select Calendar or Event list as the default view for the events loop
* Default Country: Select the default country for the admin
* Embed Google Maps: Turn on Google Maps and define the height and width of the map.
* Date / Time format is now managed via the default wordpress setting

= Requirements =

PHP 5.1 OR ABOVE

== Documentation ==

The built in template can be overridden by files within your template.

= Default vs. Custom Templates =

The Events Calendar plugin now comes with default templates for the list view, grid view and single post view. If you would like to alter them, create a new folder called "events" in your template directory and copy over the following files from within the plugin folder (simple-events/views/):

* gridview.php
* list.php
* single.php
* events-list-load-widget-display.php

Edit the new files to your hearts content. Please do not edit the one's in the plugin folder as that will cause conflicts when you update the plugin to the latest release.

= Supported Variables and URLs =

This plugin registers the following rewrite rules, which controls which posts are available in the loop.  The number of posts returned defaults to 10, but is configurable by the $count parameter to get_events().

Events/Upcoming 
&cat=<eventcategory>&eventDisplay=upcoming
  
Displays events starting today in ascending date order.
  
Events/Past
&cat=<eventcategory>&eventDisplay=past

Displays events that started before today in descending date order.
  
Events/2010-01-02
&cat=<eventcategory>&eventDisplay=bydate&eventDate=2010-01-02

Displays only events that start on Jan 2, 2010.

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

**is_event( $id )**

Returns true or false if the current post is an event.  ID is optional.

**is_featured_event( $id )**

Returns true or false if the current post is a featured event.  ID is optional.

**event_style( $id )**
**get_event_style( $id )**

Echos or returns, respectively, the event class specified in the admin panel.  ID is optional.

**is_new_event_day()**

Called inside of the loop, returns true if the current post's meta_value (EventStartDate) is different than the previous post.   Will always return true for the first event in the loop.

**get_events( $count )**

Call this function in a template to query the events and start the loop.   Do not subsequently call the_post() in your template, as this will start the loop twice and then you're in trouble.

http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query#Query_based_on_Custom_Field_and_Category

**events_displaying_past()**

Returns true if the query is set for past events

For those of you who have the Eventbrite plugin turned on:

**event_google_map_embed( $id, $width, $height)**

Returns an embedded google map. Width and height are set through the admin panel unless overridden directly through the function call.

**the_event_tickets( $id, $width, $height)**

Returns an EventBrite.com embedded ticket sales inline (not wordpress) widget 

= Top of the Loop Cron =

On the day of the event (at midnight) the plugin runs a cron which updates the post date to show the even at the top of the loop.

== Screenshots ==

1. Grid View Template
1. List View Template
1. Single Post Template
1. Settings Panel
1. Post (Event) Editor
1. Widget Admin
1. Unstyled Widget

== FAQ ==

= Where do I go to file a bug or ask a question? =

Please visit the forum for questions or comments: http://wordpress.org/tags/the-events-calendar/

== Changelog ==

= 1.6 =

Features

* Child Theme support 
* iCal Feed of all events now accessible using http://<yourUrlHere>.com/?ical
* Setting to include / exclude events form general loop
* Subcategories in events now behave properly! (and include some css for your creative endeavors)
* Get Events function no longer starts its own loop (significantly reduce conflicts with other plugins)
* Added class to current day
* PHP versions older than 5.1 will fail gracefully
* Uninstall file
* Calendar grid view now honors 'posts_per_page' wordpress setting.
* Calendar grid view now has "previous" and "next" month links
* Widget now has options to control behavior when there are no events to display
* Updates to widget layout (links to events and "read more")
* It is now possible to select no default country for events
* Added a setting to control "pretty urls" to the events vs using query args (reduce conflicts with other rewrite rules)
* Default times for new event updated (all day, starting tomorrow)

Translations

* German [Felix Bartels]
* Brazilian Portuguese [Thiago Abdalla]
* Dutch [Sjoerd Boerrigter]
* Spanish [Los Jethrov]
* Updates to Swedish Translation [Kaj Johansson]
* Updates to German Translation [Andre Schuhmann]
* Danish [Carsten Matzon]

Bugs

* Improvements to field validation
* Fixes Embedded Map HTML URL Encoding so its w3c compatible (Thanks azizur!)
* Usability issue: Is this post an event? Yes/No -- now you can click the text and it will select your choice. (Thanks azizur!)
* Fixes drag/drop issue (Thanks azizur!)
* State vs Province meta values were not mutually exclusive
* HTML was not properly escaped in the template
* Fixes PHP short tag issue in one of the templates
* in single.php, the Back to Events link no longer strictly goes to the grid view, but adheres to the default few option -pointed out by azzatron on the forum
* google map link is now produced with minimal information, complete address is no longer needed, W3C-compatible output
** tec_event_address() added for easy echoing of the event address
** thanks to AntonLargiader and azizur on forum
* improvement and debugging of entire error catching and displaying system
* Fixes upcoming/past sorting issue (Thanks Elliot Silver for the support!)


= 1.5.6 =

* Fixes date bug in the class method setOptions(), line 1188 in the-events-calendar.php. Thanks to hmarcbower for some ground work on this

= 1.5.5 =

Features

* Starting day in calendar view now reflects the start_of_week Wordpress option
* Widget view can now be overwritten in theme ([theme]/events/events-list-load-widget-display.php)
* Setting that enables "Feature on Event Date" - This option will bump an event to the top of the homepage loop on the day of the event (and then return it when over).
* Timezone to be set by wordpress settings
* Class in calendar for current Day, past events and future events.
* Fixed translation bugs (thanks to Kaj for catching)
** new .pot file available with more entries, covering more of the plugin (months are in there now)

Translations

* Polish [Maciej Swoboda]
* Czech [Tomas Vesely]

Bugs

* RSS broken with alterate permalinks
* AM/PM time display bug
* Issue of right float on right three cols on cal view for some themes

= 1.5.4 =

A huge thanks to our first round of translators! They helped us tackle localization and become a multilingual application. If you have any interest in translating, grab the .pot file in the /lang/ folder and then send us a completed copy. I'd like to suggest you start a thread in the forum so people know you are working on it and can collaborate.

Also, welcome some new contributors:

Dan Cameron, who has worked with us to help makes the default template more theme friendly.

Aaron Rhodes, who has begun doing qa for each release. He has been catching bugs left and right and hopefully will make our releases smoother. You'll be seeing him on the forum.

* Fixed localization functions so that translation files work
* Translation files for:
** Swedish: provided by Kaj Johansson
** French: provided by Benjamin Nizet (Enseignons)
** Italian: provided by Maurizio Lattanzio
* Smarter date chooser provides only those dates which the month contains as choices, accounts for leap years - fixes multi-month event bug pointed out by coold78 on the forum
* New system for date and time formatting
** Optional format string argument for event_start_date() or event_end_date(). Otherwise, the format set in WP options is used.
** Time format is is determined by WP options.
* Removed donate button from User Profile view
* Cost now defaults to NULL
** On front end, cost field disappears if its value is NULL
* More robust path for the ajax that hides the donation button to fix non standard wordpress install locations
* Added comments to default event template + global on/off toggle in settings for comments on events posts
* Set venue label to only display if there is a venue
* Standardized ids and classes in bundled templates
** Removed camelCase IDs and classes
** Add "tec-" to the beginning of all ids and classes with the templates

= 1.5.3 =

Settings

* updated minimum php requirement to 5.1

Bug Fixes

* Fixed permalink issue on calendar
* Removed limit of events shown on calendar view
* Fixed conflicting namespace error with xml2array function
* Curl support for (soon to be deprecated) safe_mod

Features

* Update minuted to increment by 5 rather than 1 and default to 00
* Added a class per category to each event in the grid view: "cat_classname" to allow users more styling controls in the grid.
* Added embedded google maps integration & admin panel controls
* Changed "Grid view" to "Calendar", "List view" to "Event List"
* Made significant headway on preparing translation - more to go
* Clean up admin quite a bit to make it easier to navigate the events form

= 1.5.2 =

* Updated ticket display to hide after event end date
* Fix exception handling bugs

= 1.5.1 =

* Updated single.php to improve dependency on eventbrite
* Updated cost function to use filter

= 1.5 =

* Fixed a whole pile of small bugs.
* Extract Eventbrite from the Events calendar into a stand alone plugin
* Add donate links
* Add settings panel
** Default View (calendar or list) for categories
** Default country for events
** Donate toggle on/off
* Upgrade for WP 2.9

= 1.5 alpha =

* Plug and Play install including default templates (list view, grid view and post)
* Theme overwrite of default templates (see instructions)
* 12 hour / 24 hour time display options
* Work with all permalink styles
* Hide data from custom fields
* Hide Eventbrite sales box in post if there is are tickets
* Multiple javascript bug fixes
* Pull price for 1st ticket from general event price
* Add some basic error messages from Eventbrite (much more to come)
* Remove dependencies on other S&P plugins

= 1.4.1 =

* Featured event checkbox and template tag is_featured_event()

= 1.4 =

* Grid View
* Additional Internationalization support added

= 1.3 =

* Built events list widget

= 1.2 =

* Started internationalization (translation) support
* Added international addresses
* Extracted from S&P core plugin to stand alone.