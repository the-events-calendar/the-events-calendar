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
 * @since TBD Alter template to handle multiple links.
 *
 * @var array $subscribe_links List of links to display with associated data.
 */
if ( empty( $subscribe_links ) ) {
	return;
}


use Tribe\Events\Views\V2\iCalendar\iCalendar_Handler;
use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;

/* @var Tribe\Events\Views\V2\iCalendar\iCalendar_Handler $handler */
$handler = tribe( iCalendar_Handler::class );
$view    = $this->get_view();
$count   = array_filter(
	$subscribe_links,
	static function( Link_Abstract $link_obj ) use ( $view ) {
		return $link_obj::is_visible( $view );
	}
);

if ( ! $handler->use_subscribe_links() || 1 === count( $count ) ) : ?>
	<?php $key = array_keys( $count )[0]; ?>
	<?php $this->template( 'components/subscribe-links/single', [ 'item' => $subscribe_links[ $key ] ] ); ?>
<?php else : ?>
	<?php $this->template( 'components/subscribe-links/list', [ 'items' => $subscribe_links ] ); ?>
<?php endif; ?>
