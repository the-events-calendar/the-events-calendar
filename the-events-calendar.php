<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar open source plugin empowers you to rapidly create and manage events using the post editor. Google Maps, microformats for SEO, calendar grid view and event list view, widgets and much more. <a href="http://tri.be/wordpress-events-calendar/?ref=tec-plugin">Check out the full feature list</a>. Need more features, peruse the selection of Add-Ons.
 Version: 2.0.2
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be?ref=tec-plugin
 Text Domain: tribe-events-calendar
 */

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();
