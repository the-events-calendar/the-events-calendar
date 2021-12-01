<?php
/**
 * Component: iCal Link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/ical-link.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 * @since 5.12.0 Alter template to handle multiple links.
 *
 * @var array $subscribe_links List of links to display with associated data.
 *
 * Legacy data:
 * @var object $ical Object containing iCal data
 */

use Tribe\Events\Views\V2\iCalendar\iCalendar_Handler;
use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;

/* @var Tribe\Events\Views\V2\iCalendar\iCalendar_Handler $handler */
$handler = tribe( iCalendar_Handler::class );

if ( $handler->use_subscribe_links() && empty( $subscribe_links ) ) {
	return;
}

if ( ! $handler->use_subscribe_links() && empty( $ical->display_link ) ) {
	return;
}

// Users can turn off the link list via a filter, handle that.
if ( ! $handler->use_subscribe_links() ) {
	$this->template( 'components/subscribe-links/legacy', [ 'ical' => $ical ] );

	return;
}

$view  = $this->get_view();
$count = array_filter(
	$subscribe_links,
	static function( Link_Abstract $link_obj ) use ( $view ) {
		return $link_obj->is_visible( $view );
	}
);

if ( 1 === count( $count ) ) {
	// If we only have one link in the list, show a "button".
	$key = array_keys( $count )[0];
	$this->template( 'components/subscribe-links/single', [ 'item' => $subscribe_links[ $key ] ] );
} else {
	// If we have multiple links in the list, show a "dropdown".
	$this->template( 'components/subscribe-links/list', [ 'items' => $subscribe_links ] );
}
