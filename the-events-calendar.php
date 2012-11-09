<?php
/*
Plugin Name: The Events Calendar
Description: The Events Calendar is a fully featured event management system with multiple views, 3rd party integrations, and a slew of premium add-ons.
Version: 2.0.10
Author: Modern Tribe, Inc.
Author URI: http://tri.be?ref=tec-plugin
Text Domain: tribe-events-calendar
License: GPLv2 or later
*/

/*
Copyright 2009-2012 by Modern Tribe Inc and the contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once( dirname(__FILE__) . '/lib/the-events-calendar.class.php' );

TribeEvents::instance();

require_once( dirname(__FILE__) . '/lib/tribe-presstrends.php' );

register_deactivation_hook( __FILE__, array( 'TribeEvents', 'resetActivationMessage' ) );