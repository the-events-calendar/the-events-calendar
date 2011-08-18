<?php
/*
 Plugin Name:  The Events Calendar
 Description:  The Events Calendar plugin enables you to rapidly create and manage events using the post editor. Features include optional Eventbrite integration, Google Maps integration as well as default calendar grid and list templates for streamlined one click installation. When updating Events Calendar Pro, if Event Tickets Pro is being used, the two plugins must be updated together. Requires WordPress 3.0 (or higher) and PHP 5.2 (or above). 
 Version: 2.0
 Author: Tribe Pro
 Author URI: http://tribe.pro
 Text Domain: tribe-events-calendar
 */


register_activation_hook(__FILE__, 'events_calendar_pro_activate');

define( 'SP_EVENTS_SUPPORTED_WP_VERSION', version_compare(get_bloginfo('version'), '3.0', '>=') );
define( 'SP_EVENTS_SUPPORTED_PHP_VERSION', version_compare( phpversion(), '5.2', '>=') );

if ( ! function_exists('events_calendar_pro_activate') ) {
	function events_calendar_pro_activate() {
		if ( SP_EVENTS_SUPPORTED_WP_VERSION && SP_EVENTS_SUPPORTED_PHP_VERSION ) {
			events_calendar_pro_load();
			$tribe_ecp = Events_Calendar_Pro::instance();
			$tribe_ecp->on_activate();
		}
	}
}

if ( ! function_exists('events_calendar_pro_load') ) {
	function events_calendar_pro_load() {
		if ( SP_EVENTS_SUPPORTED_WP_VERSION && SP_EVENTS_SUPPORTED_PHP_VERSION ) {
			$lib = dirname(__FILE__) . '/lib/';
			require_once( $lib .'events-calendar-pro.class.php' );
			require_once( $lib .'the-events-calendar-exception.class.php' );
			require_once( $lib .'template-tags.php' );
			require_once( $lib .'template-tags-deprecated.php' );
			require_once( $lib .'events-calendar-widget.class.php' );
			require_once( $lib .'events-featured-widget.class.php' );
			require_once( $lib .'events-list-widget.class.php' );
			require_once( $lib .'tribe-admin-events-list.class.php' );
			require_once( $lib .'tribe-date-utils.class.php' );
			require_once( $lib .'tribe-ecp-templates.class.php' );
			require_once( $lib .'tribe-event-api.class.php' );
			require_once( $lib .'tribe-event-query.class.php' );
			require_once( $lib .'tribe-the-events-calendar-import.class.php' );
			require_once( $lib .'tribe-view-helpers.class.php' );
		}
	}
}

events_calendar_pro_load();

add_action('admin_head', 'tribe_events_notices');
function tribe_events_notices() {
	if ( ! SP_EVENTS_SUPPORTED_WP_VERSION ) {
		echo '<div class="error"><p>Events Calendar Pro requires WordPress 3.0 or higher. Please upgrade WordPress or deactivate Events Calendar Pro.</p></div>';
	}
	if ( ! SP_EVENTS_SUPPORTED_PHP_VERSION ) {
		echo '<div class="error"><p>Events Calendar Pro requires PHP 5.2 or higher. Talk to your Web host about not living in the past.</p></div>';
	}
}
