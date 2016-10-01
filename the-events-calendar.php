<?php
/*
Plugin Name: The Events Calendar
Description: The Events Calendar is a carefully crafted, extensible plugin that lets you easily share your events. Beautiful. Solid. Awesome.
Version: 4.3rc4
Author: Modern Tribe, Inc.
Author URI: http://m.tri.be/1x
Text Domain: the-events-calendar
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

define( 'THE_EVENTS_CALENDAR_DIR', dirname( __FILE__ ) );
define( 'THE_EVENTS_CALENDAR_FILE', __FILE__ );

include THE_EVENTS_CALENDAR_DIR . '/src/Tribe/Main.php';
if ( class_exists( 'Tribe__Events__Main' ) ) {
	Tribe__Events__Main::instance();
}
