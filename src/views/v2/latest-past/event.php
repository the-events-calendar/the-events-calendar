<?php
/**
 * View: Latest Past Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/latest-past/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.1.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$container_classes = [ 'tribe-common-g-row', 'tribe-events-calendar-latest-past__event-row' ];
$container_classes['tribe-events-calendar-latest-past__event-row--featured'] = $event->featured;

$event_classes = tribe_get_post_class( [ 'tribe-events-calendar-latest-past__event', 'tribe-common-g-row', 'tribe-common-g-row--gutters' ], $event->ID );
?>
<div <?php tribe_classes( $container_classes ); ?>>

	<?php $this->template( 'latest-past/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-latest-past__event-wrapper tribe-common-g-col">
		<article <?php tribe_classes( $event_classes ) ?>>
			<?php $this->template( 'latest-past/event/featured-image', [ 'event' => $event ] ); ?>

			<div class="tribe-events-calendar-latest-past__event-details tribe-common-g-col">

				<header class="tribe-events-calendar-latest-past__event-header">
					<?php $this->template( 'latest-past/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'latest-past/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'latest-past/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'latest-past/event/description', [ 'event' => $event ] ); ?>
				<?php $this->template( 'latest-past/event/cost', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
