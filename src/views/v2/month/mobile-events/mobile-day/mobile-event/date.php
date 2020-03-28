<?php
/**
 * View: Month View - Mobile Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/mobile-day/mobile-event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.10
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
use Tribe__Date_Utils as Dates;

$time_format = tribe_get_time_format();
$event_date_attr = $event->dates->start->format( Dates::DBDATEFORMAT );
?>
<div class="tribe-events-calendar-month-mobile-events__mobile-event-datetime tribe-common-b2">
	<?php if ( ! empty( $event->featured ) ) : ?>
		<em
			class="tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
			title="<?php esc_attr_e( 'Featured', 'the-events-calendar' ) ?>"
		>
		</em>
		<span class="tribe-events-calendar-month-mobile-events__mobile-event-datetime-featured-text">
			<?php esc_html_e( 'Featured', 'the-events-calendar' ); ?>
		</span>
	<?php endif; ?>
	<?php if ( $event->all_day ) : ?>
		<time datetime="<?php echo esc_attr( $event->dates->start->format( Dates::DBDATEFORMAT ) ) ?>">
			<?php esc_html_e( 'All day', 'the-events-calendar' ); ?>
		</time>
	<?php else : ?>
		<time datetime="<?php echo esc_attr( $event_date_attr ); ?>">
			<?php echo $event->schedule_details->value(); ?>
		</time>
	<?php endif; ?>
	<?php $this->template( 'month/mobile-events/mobile-day/mobile-event/date/meta', [ 'event' => $event ] ); ?>
</div>
