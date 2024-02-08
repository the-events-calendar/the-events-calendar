<?php
/**
 * Block: Event Links
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-links` *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 * @var Tribe__Events__Editor__Template $this
 */

// don't show on password protected posts
use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;

if ( post_password_required() ) {
	return;
}

$has_google_cal   = $this->attr( 'hasGoogleCalendar' );
$has_ical         = $this->attr( 'hasiCal' );
$has_outlook_365  = $this->attr( 'hasOutlook365' );
$has_outlook_live = $this->attr( 'hasOutlookLive' );

// don't show on password protected posts
if ( post_password_required() ) {
	return;
}

$has_google_cal   = $this->attr( 'hasGoogleCalendar' );
$has_ical         = $this->attr( 'hasiCal' );
$has_outlook_365  = $this->attr( 'hasOutlook365' );
$has_outlook_live = $this->attr( 'hasOutlookLive' );

$subscribe_links = empty( $this->get( ['subscribe_links'] ) ) ? false : $this->get( ['subscribe_links'] );
// Just bail.
if ( empty( $subscribe_links ) ) {
	return;
}

$subscribe_links = array_filter(
	$subscribe_links,
	function( $item ) {
		return $item instanceof Link_Abstract
			&& isset( $item->block_slug )
			&& $this->attr( $item->block_slug );
	}
);

if ( empty( $subscribe_links ) ) {
	return;
}

remove_filter( 'the_content', 'do_blocks', 9 );

if ( 1 === count( $subscribe_links ) ) {
	// If we only have one link in the list, show a "button".
	$item = array_shift( $subscribe_links );
	$this->template( 'blocks/parts/subscribe-single', [ 'item' => $item ] );
} else {
	// If we have multiple links in the list, show a "dropdown".
	$this->template( 'blocks/parts/subscribe-list', [ 'items' => $subscribe_links ] );
}