<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar is a fully featured event management system with multiple views, 3rd party integrations, and a slew of premium add-ons.
 Version: 2.0.4
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be?ref=tec-plugin
 Text Domain: tribe-events-calendar
 */

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();
