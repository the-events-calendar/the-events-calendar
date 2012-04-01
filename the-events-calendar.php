<?php
/*
 Plugin Name: The Events Calendar
 Description: The Events Calendar open source plugin empowers you to rapidly create and manage events using the post editor. Google Maps, microformats for SEO, calendar grid view and event list view, widgets and much more. <a href="http://tri.be/wordpress-events-calendar/?ref=tec-plugin">Check out the full feature list</a>. Need more features, peruse the selection of Add-Ons.
 Version: 2.1-alpha
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