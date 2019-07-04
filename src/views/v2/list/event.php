<?php
/**
 * View: List Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-common-g-row', 'tribe-common-g-row--gutters', 'tribe-events-calendar-list__event-row' ];

if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-calendar-list__event-row--featured';
}

?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'list/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
		<article class="tribe-events-calendar-list__event tribe-common-g-row tribe-common-g-row--gutters">
			<?php $this->template( 'list/event/featured-image', [ 'event' => $event ] ); ?>

			<div class="tribe-events-calendar-list__event-details tribe-common-g-col">

				<header class="tribe-events-calendar-list__event-header">
					<?php $this->template( 'list/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'list/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'list/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'list/event/description', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
