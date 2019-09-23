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
 * @version 4.9.9
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 */
use Tribe__Date_Utils as Dates;

$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );

?>
<div class="tribe-events-calendar-day__event-datetime-wrapper tribe-common-b2">
	<?php if ( $event->featured ) : ?>
		<em
			class="tribe-events-calendar-day__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ); ?>"
		>
		</em>
		<span class="tribe-events-calendar-day__event-datetime-featured-text tribe-common-a11y-visual-hide">
			<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
		</span>
	<?php endif; ?>
	<time class="tribe-events-calendar-day__event-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<?php echo $event->schedule_details->value(); ?>
	</time>
	<?php $this->template( 'day/event/date/meta', [ 'event' => $event ] ); ?>
</div>
