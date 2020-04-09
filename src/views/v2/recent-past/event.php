<?php
/**
 * View: Recent Past Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/recent-past/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$container_classes = [ 'tribe-common-g-row', 'tribe-events-calendar-list__event-row' ];
$container_classes['tribe-events-calendar-list__event-row--featured'] = $event->featured;

$event_classes = tribe_get_post_class( [ 'tribe-events-calendar-list__event', 'tribe-common-g-row', 'tribe-common-g-row--gutters' ], $event->ID );
?>
<div <?php tribe_classes( $container_classes ); ?>>

	<?php $this->template( 'recent-past/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
		<article <?php tribe_classes( $event_classes ) ?>>

			<div class="tribe-events-calendar-list__event-details tribe-common-g-col">

				<header class="tribe-events-calendar-list__event-header">
					<?php $this->template( 'recent-past/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'recent-past/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'recent-past/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'recent-past/event/description', [ 'event' => $event ] ); ?>
				<?php $this->template( 'recent-past/event/cost', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
