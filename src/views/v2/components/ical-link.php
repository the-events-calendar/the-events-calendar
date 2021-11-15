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
 *
 * @var object $ical Object containing iCal data
 */

use Tribe\Events\Views\V2\iCalendar\Subscribe;

$subscribe = tribe( Subscribe::class );
$count     = array_filter( wp_list_pluck( $subscribe_links, 'display' ) );

if ( ! $subscribe->use_subscribe_links() || 1 === count( $count ) ) : ?>
	<?php $key = array_keys( $count )[0]; ?>
	<?php $this->template( 'components/subscribe-links/single', [ 'item' => $subscribe_links[ $key ] ] ); ?>
<?php else : ?>
	<?php $this->template( 'components/subscribe-links/dropdown', [ 'items' => $subscribe_links ] ); ?>
<?php endif; ?>
