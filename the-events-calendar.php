<?php
/*
 Plugin Name:  Events Calendar Pro
 Description:  The Events Calendar plugin enables you to rapidly create and manage events using the post editor. Features include optional Eventbrite integration, Google Maps integration as well as default calendar grid and list templates for streamlined one click installation. When updating The Events Calendar, if EventBrite for The Events Calendar is being used, the two plugins must be updated together. Requires WordPress 3.0 (or higher) and PHP 5.2 (or above). 
 Version: 1.2b
 Author: Shane & Peter, Inc.
 Author URI: http://www.shaneandpeter.com/
 Text Domain: the-events-calendar
 */


register_activation_hook(__FILE__, 'the_events_calendar_activate');

define( 'SP_EVENTS_SUPPORTED_WP_VERSION', version_compare(get_bloginfo("version"), '3.0', '>=') );
define( 'SP_EVENTS_SUPPORTED_PHP_VERSION', version_compare( phpversion(), '5.2', '>=') );


if ( ! function_exists('the_events_calendar_activate') ) {
	function the_events_calendar_activate() {
		if ( SP_EVENTS_SUPPORTED_WP_VERSION && SP_EVENTS_SUPPORTED_PHP_VERSION ) {
			the_events_calendar_load();
			global $spEvents;
			$spEvents->on_activate();
		}
	}
}

if ( ! function_exists('the_events_calendar_load') ) {
	function the_events_calendar_load() {
		if ( SP_EVENTS_SUPPORTED_WP_VERSION && SP_EVENTS_SUPPORTED_PHP_VERSION ) {
			$events_dir = dirname(__FILE__);
			require_once($events_dir . "/the-events-calendar.class.php");
			require_once($events_dir . "/the-events-calendar-exception.class.php");
			require_once($events_dir . "/events-calendar-widget.class.php");
			require_once($events_dir . "/events-list-widget.class.php");
			require_once($events_dir . "/template-tags.php");
		}
	}
}

the_events_calendar_load();

add_action('admin_head', 'sp_events_notices');
function sp_events_notices() {
	if ( ! SP_EVENTS_SUPPORTED_WP_VERSION ) {
		echo "<div class='error'><p>Events Calendar Pro requires WordPress 3.0 or higher. Please upgrade WordPress or deactivate Events Calendar Pro.</p></div>";
	}
	if ( ! SP_EVENTS_SUPPORTED_PHP_VERSION ) {
		echo "<div class='error'><p>Events Calendar Pro requires PHP 5.2 or higher. Talk to your Web host about not living in the past.</p></div>";
	}
}