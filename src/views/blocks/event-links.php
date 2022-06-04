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

remove_filter( 'the_content', 'do_blocks', 9 );
$subscribe_links = empty( $this->get( ['subscribe_links'] ) ) ? false : $this->get( ['subscribe_links'] );

$should_render  = $subscribe_links && ( $has_google_cal || $has_ical || $has_outlook_365 || $has_outlook_live );

if ( $has_google_cal ) {
	if ( $this->get( [ 'subscribe_links', 'gcal' ] ) instanceof Link_Abstract ) {
		$google_cal_link = $subscribe_links['gcal']->get_uri( null );
	} else {
		$google_cal_link = Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() );
	}
}

if ( $has_ical && $this->get( [ 'subscribe_links', 'ical' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'ical' ] );
}

if ( $has_outlook_365 && $this->get( [ 'subscribe_links', 'outlook-365' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'outlook-365' ] );
}

if ( $has_outlook_live && $this->get( [ 'subscribe_links', 'outlook-live' ] ) instanceof Link_Abstract ) {
	$items[] = $this->get( [ 'subscribe_links', 'outlook-live' ] );
}

if ( empty( $items ) ) {
	return;
}

if ( 1 === count( $items ) ) {
	// If we only have one link in the list, show a "button".
	$item = array_shift( $items );
	$this->template( 'blocks/parts/subscribe-single', [ 'item' => $item ] );
} else {
	// If we have multiple links in the list, show a "dropdown".
	$this->template( 'blocks/parts/subscribe-list', [ 'items' => $items ] );
}
