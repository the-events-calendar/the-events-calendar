=== The Events Calendar ===

Contributors: [Current Contributors], shane.pearlman, peterchester, reid.peifer, roblagatta, jkudish, Nick Ciske, Paul Hughes, [Past Contributors], kelseydamas, mattwiebe, dancameron, jgadbois, Justin Endler, [Produced By Modern Tribe Inc], ModernTribe
Tags: modern tribe, tribe, widget, events, tooltips, grid, month, list, calendar, recurring, event, venue, eventbrite, registration, tickets, ticketing, eventbright, api, dates, date, plugin, posts, sidebar, template, theme, time, google maps, conference, workshop, concert, meeting, seminar, summit, forum, shortcode
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QA7QZM4CNQ342
Requires at least: 3.1
Tested up to: 3.3
Stable tag: 2.0.3

== Description ==

IMPORTANT NOTICE: If you are upgrading from a pre 2.0 version of The Events Calendar, please BACK UP your data before upgrading! This is a significant update.

The Events Calendar plugin enables you to rapidly create and manage events. Features include Google Maps integration as well as default templates such as a calendar grid and event list, widget and so much more. Looking for recurring events, the ability to sell tickets, manage conference schedules, accept user submitted events automatically and more? Check out the <a href="http://tri.be/products/?ref=tec-readme">available premium and community add-ons</a>. Have questions or looking to get help from our active user community, <a href="https://www.facebook.com/ModernTribeInc">join us on Facebook</a>, sign up for our newsletter (bottom of the home page) or check out <a href="http://tri.be/support/?ref=tec-readme">our support page</a>. Please note that while we are actively supporting this plugin, we don't provide support for non-paying users.

Just getting started? Check out our <a href="http://tri.be/support/documentation/events-calendar-pro-new-user-primer/?ref=tec-readme">new user primer!</a>

= The Events Calendar 2.0 =

* Event custom post type
* Easily manage events
* Upcoming Events Widget
* Provides full template to complement the 2010 & 2011 theme out of the box (month and list view)
* Extensive template tags for customization
* MU Compatible
* Google Maps Integration
* Calendar Month view with tooltips
* Includes support for venue, cost, address, start and end time, google maps link

= Events Calendar Pro Features =

* Recurring events
* Saved venues & organizers
* Custom events attributes
* Advanced events manager
* Venue view
* Single day view
* Ajax calendar
* Advanced widgets
* Gcal / ical user download (import)
* and lots more.

<a href="http://tri.be/wordpress-events-calendar-pro/?ref=tec-readme">Grab a copy of Events Calendar Pro!</a>

== Screenshots ==

1. Calendar View
1. List View
1. Single Post
1. Event Editor
1. Event List Admin
1. Settings Panel

== Installation ==

= Install =

1. <a href="http://tri.be/downloading-installing-activating-the-events-calendar-2-0-pro-2-0/?ref=tec-readme">Follow the directions in our simple video</a>
1. Update your permalinks to ensure that the event specific rewrite rules take effect.
1. If you have trouble installing, see the [Codex](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation) for more helpful info.

For more information, check out our <a href="http://tri.be/support/documentation/events-calendar-pro-new-user-primer/?ref=tec-readme">new user primer!</a>


= Activate =

No setup required. Just plug and play!

= Requirements =

* PHP 5.2 or above
* WordPress 3.1 or above

== Documentation ==

For template tags, you can view our template tag includes in the "public" folder to read through the functions directly or visit our online documentation at <a href="http://tri.be/support/documentation?ref=tec-readme">http://tri.be/support/documentation</a>

== Changelog ==

= 2.0.3 =

**Small features, UX and Content Tweaks:**

* Incorporated get_tribe_custom(ÔField labelÕ) to code base
* Code updated to account for additional translation strings
* Made the $sep param of TribeEvents::maybeAddEventTitle() optional

**Bug Fixes:**

* Organizer data can now be changed on an already-published entry in core
* tribe_get_start_date() fixed
* Future instances of custom recurrence no longer display inaccurate start/end times
* In views/single.php on lines 58, 60 & 62, changed tag to <? instead of <?php
* Required changes to WP_PLUGIN_URL (line 126) & WP_CONTENT_URL (line 930) in the.events.calendar.class.php
* Deleting a single instance of recurrence in PRO (whether from the list or from within an entry) now works correctly with appropriate prompts/dialogue boxes
* Attempting to change from a saved organizer/venue to no organizer/venue now works
* Venue no longer behaves bizarrely when no address data added
* Fixed issue where event links broke for recurring events whenever a site had permalinks set to default; also fixed general conflicts that occurred when URL rewriting was off
* Customized defaults can now be turned off after being enabled under Settings -> The Events Calendar
* Removed instances where organizer data displayed as event title for some users
* Changes made to custom fields under Settings -> The Events Calendar now take effect upon save
* Non-U.S. states and provinces now save correctly
* General fixes to improve how default venues/organizers function and are modified
* Addressed various PHP notices


= 2.0.2 =

**Small features, UX and Content Tweaks:**

* Added link to new user primer (http://tri.be/support/documentation/events-calendar-pro-new-user-primer?ref=tec-readme) to the activation message.
* Added tribe_is_event_in_category conditional to plugin code base.
* Plugin now adds a default role when registering custom post types.
* Russian language files incorporated (free & PRO) from Mikhail Michouris
* Dutch translation files incorporated (free only) from Rick van Dalen
* Danish translation files incorporated (PRO only) from Christian Andersen
* Italian translation files incorporated (free & PRO) from Stefano Castelli

**Bugs:**

* Months will now show appropriate day count, instead of 31 days as they were previously.
* Custom recurring events previously not showing start AND end time (just start time); now are showing both.
* Hack to include events in your main loop no longer causes event link to vanish.
* Fixed issue of recurrence settings changing upon publication.
* Fixed other bug related to recurrence details showing incorrect date/time.
* General bugs with weekly recurrence have been squashed.
* Admin page should no longer hang when updating a recurring event.
* Breadcrumbs will now show the correct slug info on Thesis.
* Not entering a name for an organizer or venue doesn't stop it from publishing, as it did previously.
* Admin events list now appears with soonest event at the top, not the bottom.
* Deleting instances of recurrence now works within individual entries.
* Unnamed venue/organizer now created when no venue or organizer name added.
* Featured image no longer overlaps the map on individual entries in the default 2011 theme.
* Custom recurrence events weren't previously showing end time on the frontend; they will now.
* Comments box now appears on the default page template (was previously only on default events template).
* Minor change to line 1835 of the-events-calendar.class.php.
* Incorporated patch to include file name in permalink for users running the plugin on shared IIS servers.
* Changes to incorrect tag on lines 58, 60 & 62 in views/single.php.
* Next/Previous link in recurring & standalone events both work fine.
* General display tweaks to Calendar widget (wasn't showing future events previously, and CSS was screwy)
* Renamed the dashboard Tribe newsfeed widget to conform with rebranding efforts.
* Worked to better display comments in Thesis & Genesis themes.
* General display bugs related to the WP 3.3 beta.
* Fixed general PHP notices that appeared with debug turned on in your wp-config file.

= 2.0.1 =

**Small features, UX and Content Tweeks:**

* Enabled method to turn off event upsell messages on your site in wp-config.php - define( 'TRIBE_HIDE_UPSELL', true );
* Updated migration message to help 1.6.5 users have an easier time when they upgrade to 2.0
* Added a "View my events" link in the settings panel to help users find where the calendar lives
* Added Russian and Swedish translation files.
* Broke out advanced functions into their own file "advanced-functions.php"
* Added in line documentation to all template tags and moved them to separate files in the /public folder
* Added and updated documentation on http://tri.be/support/documentation/

**Bugs:**

* Added "00" in the time drop down when in 24 hour mode
* Updated default end time to "17" for 24 hour mode
* Fixed broken link in the "you need events 2.0 open source" on activation for PRO users.
* More tag now properly crops content in loop
* Custom meta > number only drop down values now carry over
* Resolved an issue where single day view yielded a 404 if date was in the past
* Next event widget now shows the proper event
* Attachments on recurring events now persist across instances
* Custom recurring event error caused by blank end date fixed
* Default state now shows properly
* Title tags wrong in various views fixed
* Event date showing incorrectly for certain cases of recurrence fixed.
* Venue / Organizer data not saving for certain cases of recurrence fixed.

= 2.0 =

This is such a major re-write that we are starting the change log over.

== Upgrade Notice ==

= 2.0.3 =

2.0.3 is a minor bug patch for 2.0. Are you upgrading from 1.6.5? Events 2.0 is a MAJOR upgrade, please backup your data and plan a little time in case you have to make any theme edits. Check out the upgrade tutorials in support on the tri.be website.