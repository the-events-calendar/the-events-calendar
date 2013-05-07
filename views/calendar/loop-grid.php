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

$days_of_week = tribe_events_get_days_of_week();
$week = 0;

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
		<tr>
		<?php while (tribe_events_have_calendar_days()) : tribe_events_the_calendar_day(); ?>
			<?php if ($week != tribe_events_get_current_week()) : $week++; ?>
		</tr>
		<tr>
			<?php endif; ?>
			<td class="<?php tribe_events_the_calendar_day_classes() ?>">
				<?php tribe_get_template_part('calendar/single', 'day') ?>
			</td>
		<?php endwhile; ?>
		</tr>
	</tbody>
</table><!-- .tribe-events-calendar -->
<?php do_action('tribe_events_calendar_after_the_grid') ?>