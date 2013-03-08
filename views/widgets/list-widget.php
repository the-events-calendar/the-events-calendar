<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * You can customize this view by putting a replacement file of the same name
 * (/widgets/list-widget.php) in the tribe-events/pro/widgets/ directory of your theme.
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

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$the_content_args = array(
	'venue' => $venue, 
	'address' => $address, 
	'city' => $city, 
	'region' => $region,
	'zip' => $zip, 
	'country' => $country, 
	'phone' => $phone, 
	'cost' => $cost,
	'organizer' => $organizer
	);

$event = array();
$tribe_ecp = TribeEvents::instance();
reset( $tribe_ecp->metaTags ); // Move pointer to beginning of array.
foreach( $tribe_ecp->metaTags as $tag ) {
	$var_name = str_replace( '_Event', '', $tag );
	$event[$var_name] = tribe_get_event_meta( $post->ID, $tag, true );
}

$event = (object) $event; // Easier to work with.
ob_start();
if ( !isset($alt_text) ) { $alt_text = ''; }
post_class( $alt_text,$post->ID );
$class = ob_get_clean();

// Start list widget template
echo apply_filters( 'tribe_events_pro_list_widget_before_template', $event, $class );

	// Event date
	echo apply_filters( 'tribe_events_pro_list_widget_before_the_date', $event );
	echo apply_filters( 'tribe_events_pro_list_widget_the_date', $event, $post->ID );
	echo apply_filters( 'tribe_events_pro_list_widget_after_the_date', $event );

	// Event title
	echo apply_filters( 'tribe_events_pro_list_widget_before_the_title', $event );
	echo apply_filters( 'tribe_events_pro_list_widget_the_title', $post );
	echo apply_filters( 'tribe_events_pro_list_widget_after_the_title', $event );

	// Event content
	echo apply_filters( 'tribe_events_pro_list_widget_before_the_content', $event );
	echo apply_filters( 'tribe_events_pro_list_widget_the_content', $event, $the_content_args );
	echo apply_filters( 'tribe_events_pro_list_widget_after_the_content', $event );

// End list widget template
echo apply_filters( 'tribe_events_pro_list_widget_after_template', $event );

// Clean up alt text
$alt_text = ( empty( $alt_text ) ) ? 'alt' : '';
