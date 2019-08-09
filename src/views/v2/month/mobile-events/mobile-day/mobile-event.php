<?php
/**
 * View: Month View Mobile Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day/mobile-event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$classes = [ 'tribe-events-calendar-month-mobile-events__mobile-event' ];
$classes['tribe-events-calendar-month-mobile-events__mobile-event--featured'] = $event->featured;
?>

<article <?php tribe_classes( $classes ); ?>>

	<?php $this->template( 'month/mobile-events/mobile-day/mobile-event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-month-mobile-events__mobile-event-details">

		<?php $this->template( 'month/mobile-events/mobile-day/mobile-event/date', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/mobile-events/mobile-day/mobile-event/title', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/mobile-events/mobile-day/mobile-event/cta', [ 'event' => $event ] ); ?>

	</div>
</article>
