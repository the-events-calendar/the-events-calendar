<?php 
/**
 * Calendar Grid Loop
 * This file sets up the structure for the calendar grid loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/calendar/loop-grid.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

// Initialize variables used in this template
$start_of_week = get_option('start_of_week', 0);
$days_of_week = tribe_events_get_days_of_week();

list($year, $month) = explode('-', tribe_get_month_view_date());
$date_start = mktime(12, 0, 0, $month, 1, $year); // 1st day of month as unix stamp

$rawOffset = date( 'w', $date_start ) - $start_of_week;
$previous_month_offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
echo $previous_month_offset;
$event_daily_counts = tribe_events_get_daily_counts($date_start); // Tribe_Events_Calendar_Template::get_daily_counts()

$current_day = date_i18n( 'd' );
$current_month = date_i18n( 'm' );
$current_year = date_i18n( 'Y' );

 ?>


<?php do_action('tribe_events_calendar_before_the_grid') ?>
<table class="tribe-events-calendar">
			<thead>
				<tr>
				<?php foreach($days_of_week as $day) : ?>
					<th id="tribe-events-<?php echo strtolower($day) ?>" title="<?php echo $day ?>"><?php echo $day ?></th>
				<?php endforeach; ?>
				</tr>
			</thead>
			<tbody class="hfeed vcalendar">
				<?php foreach ($days_of_week as $numeric_day => $day) ?>

				<?php endforeach; ?>
			</tbody>
<?php do_action('tribe_events_calendar_after_the_grid') ?>