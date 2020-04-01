<?php
/**
 * View: Past Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/past-events/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$container_classes = [ 'tribe-common-g-row', 'tribe-events-calendar-past-events__event-row' ];
$container_classes['tribe-events-calendar-past-events__event-row--featured'] = $event->featured;

$event_classes = tribe_get_post_class( [ 'tribe-events-calendar-past-events__event', 'tribe-common-g-row', 'tribe-common-g-row--gutters' ], $event->ID );
?>
<div <?php tribe_classes( $container_classes ); ?>>

	<?php $this->template( 'past-events/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-past-events__event-wrapper tribe-common-g-col">
		<article <?php tribe_classes( $event_classes ) ?>>
			<?php $this->template( 'past-events/event/featured-image', [ 'event' => $event ] ); ?>

			<div class="tribe-events-calendar-past-events__event-details tribe-common-g-col">

				<header class="tribe-events-calendar-past-events__event-header">
					<?php $this->template( 'past-events/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'past-events/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'past-events/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'past-events/event/description', [ 'event' => $event ] ); ?>
				<?php $this->template( 'past-events/event/cost', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
