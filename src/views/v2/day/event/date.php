<?php
/**
 * View: Day View - Single Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$event       = $this->get( 'event' );
$event_id    = $event->ID;
$is_featured = tribe( 'tec.featured_events' )->is_featured( $event_id );

?>
<div class="tribe-events-calendar-day__event-datetime-wrapper">
	<time class="tribe-events-calendar-day__event-datetime tribe-common-b2" datetime="1970-01-01T00:00:00+00:00">
		<?php echo tribe_events_event_schedule_details( $event ); ?>
	</time>
	<?php if ( $is_featured ) : ?>
		<em
			class="tribe-events-calendar-day__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
		>
		</em>
	<?php endif; ?>
</div>
