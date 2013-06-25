=== Advanced Post Manager ===

Contributors: ModernTribe, mattwiebe, jkudish, nickciske, peterchester, shanepearlman
Donate link: http://m.tri.be/4o
Tags: developer-tools, custom post, filter, column, metabox, taxonomy, wp-admin, admin, Post, post type, plugin, advanced, tribe
Requires at least: 3.2
Tested up to: 3.6-alpha
License: GPL v2
Stable Tag: 1.0.9

Turbo charge your posts admin for any custom post type with sortable filters and columns, and auto-registration of metaboxes.

== Description ==

This is a tool for developers who want to turbo-charge their custom post type listings with metadata, taxonomies, and more. An intuitive interface for adding (and saving) complex filtersets is provided, along with a drag-and-drop interface for choosing and ordering columns to be displayed. Metaboxes are also automatically generated for all your metadata-entry needs.

* Add columns to the post listing view
* Filter post listings by custom criteria
* Easily add metaboxes to custom post types
* Automatically add registered taxonomies to post listings
* Sort by post metadata

See docs/documentation.html in the plugin directory for full documentation.

This plugin is actively supported and we will do our best to help you. In return we simply as 2 things:

1. Help Out. If you see a question on the forum you can help with or have a great idea and want to code it up and submit a patch, that would be just plain awesome and we will shower your with praise. Might even be a good way to get to know us and lead to some paid work if you freelance. Also, we are happy to post translations if you provide them.
2. Donate - if this is generating enough revenue to support our time it makes all the difference in the world http://m.tri.be/4o

== Frequently Asked Questions ==

= Why doesn't anything happen when I activate the plugin? =

This plugin is for developers. Nothing will happen until you write some code to take advantage of the functionality it offers.

== Screenshots ==

1. The filters and columns in action
2. Automatically registered metaboxes for data entry

== Changelog ==

= 1.0.9 =

* Increase the version of the included demo plugin in order for it's update nag to go away

= 1.0.8 =

* Fix PHP notice regarding the $screen object

= 1.0.7 =

* Fix for loading JS/CSS on Windows-based servers
* Ensure the demo plugin checks that the main plugin is active to prevent white screens

= 1.0.6 =

* Add `class_exists()` conditionals to allow inclusion in 3rd-party code
* Fix a PHP notice

= 1.0.5 =

* Fix undefined indices
* Add an action for when active columns are determined

= 1.0.4 =

* CSS tweak for long select/input fields in filters
* More thorough gettext, including filterable textdomain
* Fix column bug introduced in 1.0.3

= 1.0.3 =

* Extra checks to ensure no empty columns

= 1.0.2 =

* Fix filter initialization bug

= 1.0.1 =

* Un-hide some UI elements that should show.
* Metabox HTML tweaks.

= 1.0 =

* Initial Release