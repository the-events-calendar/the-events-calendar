<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar is a fully featured event management system with multiple views, 3rd party integrations, and a slew of premium add-ons. <a href="http://tri.be/wordpress-events-calendar/?ref=tec-plugin">Check out the full feature list</a>.
 Version: 2.0.3
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be?ref=tec-plugin
 Text Domain: tribe-events-calendar
 */

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();
