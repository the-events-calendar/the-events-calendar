<?php
/**
 * View: Day Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$classes = get_post_class( [ 'tribe-common-g-row', 'tribe-common-g-row--gutters', 'tribe-events-calendar-day__event' ], $event->ID );

if ( $event->featured ) {
	$classes[] = 'tribe-events-calendar-day__event--featured';
}

?>
<article <?php tribe_classes( $classes ); ?>>
	<div class="tribe-events-calendar-day__event-content tribe-common-g-col">

		<?php $this->template( 'day/event/featured-image', [ 'event' => $event ] ); ?>

		<div class="tribe-events-calendar-day__event-details">

			<header class="tribe-events-calendar-day__event-header">
				<?php $this->template( 'day/event/date', [ 'event' => $event ] ); ?>
				<?php $this->template( 'day/event/title', [ 'event' => $event ] ); ?>
				<?php $this->template( 'day/event/venue', [ 'event' => $event ] ); ?>
			</header>

			<?php $this->template( 'day/event/description', [ 'event' => $event ] ); ?>
			<?php $this->template( 'day/event/cost', [ 'event' => $event ] ); ?>

		</div>

	</div>
</article>
