<?php
	/*
	Plugin Name: The Events Calendar
	Description: The Events Calendar is a carefully crafted, extensible plugin that lets you easily share your events. Beautiful. Solid. Awesome.
	Version: 3.9
	Author: Modern Tribe, Inc.
	Author URI: http://m.tri.be/1x
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

	require_once( dirname( __FILE__ ) . '/lib/the-events-calendar.class.php' );

	require_once( dirname( __FILE__ ) . '/lib/utils/Autoloader.php' );
	$autoloader = Tribe__Events__Autoloader::instance();
	$prefixes = array(
		'Tribe__Events__' => dirname( __FILE__ ) . '/lib',
		'Tribe__Events__Utils__' => dirname( __FILE__ ) . '/lib/utils',
		'Tribe__Events__Admin__' => dirname( __FILE__ ) . '/lib/Admin',
		'Tribe__Events__Importer__' => dirname( __FILE__ ) . '/lib/io/csv/classes',
		'Tribe__Events__PUE__' => dirname( __FILE__ ) . '/lib/pue/lib',
		'Tribe__Events__Template__' => dirname( __FILE__ ) . '/lib/template-classes',
		'Tribe__Events__Tickets__' => dirname( __FILE__ ) . '/lib/tickets',
		'Tribe__Events__Advanced_Functions__' => dirname( __FILE__ ) . '/public/advanced-functions',
	);
	$autoloader->register_prefixes( $prefixes );
	$autoloader->add_fallback_dir( dirname( __FILE__ ) . '/lib/deprecated-classes' );
	$autoloader->register_autoloader();

	Tribe__Events__Events::instance();

	register_deactivation_hook( __FILE__, array( 'Tribe__Events__Events', 'deactivate' ) );
