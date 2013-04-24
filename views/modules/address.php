<?php
/**
 * Address Module Template
 * Render an address. This is used by default in the single event view.
 *
 * This view contains the filters required to create an effective address module view.
 *
 * You can recreate an ENTIRELY new address module by doing a template override, and placing
 * a address.php file in a tribe-events/modules/ directory within your theme directory, which
 * will override the /views/modules/address.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$postId = get_the_ID();	
$address_out = array();

?>
<span class="adr">

<?php

// Get our street address
if( tribe_get_address( $postId ) ) {					
	$address_out []= '<span class="street-address">'. tribe_get_address( $postId ) .'</span>';
	if( ! tribe_is_venue() )
		$address_out []= '<span class="delimiter">,</span> ';
}

// Get our full region
$our_province = tribe_get_event_meta( $postId, '_VenueStateProvince', true );
$our_states = TribeEventsViewHelpers::loadStates();
$our_full_region = isset( $our_states[$our_province] ) ? $our_states[$our_province] : $our_province;

// Get our city
if( tribe_get_city( $postId ) ) {
	$address_out []= ' <span class="locality">'. tribe_get_city( $postId ) .'</span>';
	$address_out []= '<span class="delimiter">,</span> ';
}

// Get our region
if( tribe_get_region( $postId ) ) {
	if(count($address_out))
		$address_out []= ' <abbr class="region tribe-events-abbr" title="'. $our_full_region .'">'. tribe_get_region( $postId ) .'</abbr>';
}

// Get our postal code
if( tribe_get_zip( $postId ) ) {
	$address_out []= ' <span class="postal-code">'. tribe_get_zip( $postId ) .'</span>';
}

// Get our country
if( tribe_get_country( $postId ) ) {
	if(count($address_out))
	$address_out []= ' <span class="country-name">'. tribe_get_country( $postId ) .'</span>';
}

echo implode( '', $address_out );

?>
</span>