<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar plugin enables you to rapidly create and manage events using the post editor. Features include optional Eventbrite integration, Google Maps integration as well as default calendar grid and list templates for streamlined one click installation. When updating Events Calendar Pro, if Event Tickets Pro is being used, the two plugins must be updated together. Requires WordPress 3.0 (or higher) and PHP 5.2 (or above). 
 Version: 2.0
 Author: Modern Tribe, Inc.
 Author URI: http://tribe.pro
 Text Domain: tribe-events-calendar
 */

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();

// backwards compatability
global $sp_ecp;
$sp_ecp = TribeEvents::instance();

add_filter('generate_rewrite_rules', array(&$sp_ecp,'filterRewriteRules'));
