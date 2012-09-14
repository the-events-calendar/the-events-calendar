<?php
/**
 * @for Events Pro Featured Widget Template
 * This file contains the hook logic required to create an effective featured widget view.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @return string
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */ 

// Vars set:
// '$event->AllDay',
// '$event->StartDate',
// '$event->EndDate',
// '$event->ShowMapLink',
// '$event->ShowMap',
// '$event->Cost',
// '$event->Phone',

if ( !defined('ABSPATH') ) { die('-1'); }