<?php
/*
 Plugin Name:  The Events Calendar
 Plugin URI: http://wordpress.org/extend/plugins/the-events-calendar/
 Description:  The Events Calendar plugin enables you to rapidly create and manage events using the post editor. Features include optional Eventbrite integration, Google Maps integration as well as default calendar grid and list templates for streamlined one click installation. When updating The Events Calendar, if EventBrite for The Events Calendar is being used, the two plugins must be updated together. Requires PHP 5.1 or above. 
 Version: 1.6
 Author: Shane & Peter, Inc.
 Author URI: http://www.shaneandpeter.com/
 Text Domain: the-events-calendar
 */


register_activation_hook(__FILE__, 'the_events_calendar_activate');
add_action( 'admin_head', 'the_events_calendar_version_check' );

function the_events_calendar_version_check() {
  if ( version_compare( PHP_VERSION, "5.1", "<") ) { 
    echo "<div class='error'>The Events Calendar requires PHP 5.1 or greater.  Please de-activate The Events Calendar.</div>";
    }   
}

function the_events_calendar_activate() {
    if ( version_compare( PHP_VERSION, "5.1", "<") ) { 
        trigger_error('', E_USER_ERROR);
    } else {
		require_once(dirname(__FILE__) . "/the-events-calendar.class.php");
		require_once(dirname(__FILE__) . "/the-events-calendar-exception.class.php");
		require_once(dirname(__FILE__) . "/events-calendar-widget.class.php");
		require_once(dirname(__FILE__) . "/events-list-widget.class.php");
		require_once(dirname(__FILE__) . "/template-tags.php");
		global $spEvents;
		$spEvents->on_activate();
    }   
}

if (version_compare(phpversion(), "5.1", ">=")) {
	require_once(dirname(__FILE__) . "/the-events-calendar.class.php");
	require_once(dirname(__FILE__) . "/the-events-calendar-exception.class.php");
	require_once(dirname(__FILE__) . "/events-calendar-widget.class.php");
	require_once(dirname(__FILE__) . "/events-list-widget.class.php");
	require_once(dirname(__FILE__) . "/template-tags.php");
}