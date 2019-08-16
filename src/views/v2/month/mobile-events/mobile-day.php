<?php
/**
 * View: Month View Mobile Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$events = $day['events'];
$mobile_day_id = 'tribe-events-calendar-mobile-day-' . $day['year_number'] . '-' . $day['month_number'] . '-' . $day['day_number'];
?>

<div class="tribe-events-calendar-month-mobile-events__mobile-day" id="<?php echo sanitize_html_class( $mobile_day_id ); ?>">

	<?php $this->template( 'month/mobile-events/mobile-day/day-marker', [ 'day_date' => $day_date ] ); ?>

	<?php foreach( $events as $event ) : ?>

		<?php $this->template( 'month/mobile-events/mobile-day/mobile-event', [ 'event' => $event ] ); ?>

	<?php endforeach; ?>

</div>
