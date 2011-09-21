<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar open source plugin empowers you to rapidly create and manage events using the post editor. Google Maps, microformats for SEO, calendar grid view and event list view, widgets and much more. <a href="http://tribepro.dev.tri.be/events-calendar/features/">Checkout the full feature list</a>. Requires WordPress 3.0 (or higher) and PHP 5.2 (or above). Need more features, peruse the selection of Add-Ons.
 Version: 2.0
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be?ref=tec-plugin
 Text Domain: tribe-events-calendar
 */

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();

// backwards compatability
global $sp_ecp;
$sp_ecp = TribeEvents::instance();

add_filter('generate_rewrite_rules', array(&$sp_ecp,'filterRewriteRules'));
