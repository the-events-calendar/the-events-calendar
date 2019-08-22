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
 * @version TBD
 *
 */
use Tribe__Date_Utils as Dates;

$event       = $this->get( 'event' );
$event_id    = $event->ID;
$is_featured = tribe( 'tec.featured_events' )->is_featured( $event_id );
$event_date_attr = tribe_get_start_date( $event, true, Dates::DBDATEFORMAT );

?>
<div class="tribe-events-calendar-day__event-datetime-wrapper">
	<?php if ( $is_featured ) : ?>
		<em
			class="tribe-events-calendar-day__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
		>
		</em>
	<?php endif; ?>
	<time class="tribe-events-calendar-day__event-datetime tribe-common-b2" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<?php echo tribe_events_event_schedule_details( $event ); ?>
	</time>
</div>
