<?php 
/**
 * Mini Calendar Widget Grid Template
 * This file loads the mini calendar widget grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/mini-calendar/grid.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 
$days_of_week = tribe_events_get_days_of_week('short');
$week = 0;

?>
<div class="tribe-mini-calendar-grid-wrapper">
	<table class="tribe-mini-calendar" <?php tribe_events_the_mini_calendar_header_attributes() ?>>
		<thead class="tribe-mini-calendar-nav">
			<tr>
				<td colspan="7">
					<div>
					<?php tribe_events_the_mini_calendar_prev_link() ?>
					<span id="tribe-mini-calendar-month"><?php tribe_events_the_mini_calendar_title() ?></span>
					<?php tribe_events_the_mini_calendar_next_link() ?>
					<img id="ajax-loading-mini" src="<?php echo tribe_events_resource_url( 'images/tribe-loading.gif' ) ?>" alt="loading..." />
					</div>
				</td>
			</tr>
		</thead>

	<thead>
		<tr>
		<?php foreach($days_of_week as $day) : ?>
			<th class="tribe-mini-calendar-dayofweek"><?php echo $day ?></th>
		<?php endforeach; ?>			

		</tr>
	</thead>
	

		<tbody class="hfeed vcalendar">

			<tr>
			<?php while (tribe_events_have_month_days()) : tribe_events_the_month_day(); ?>
				<?php if ($week != tribe_events_get_current_week()) : $week++; ?>
			</tr>
			<tr>
				<?php endif; ?>
				<td class="<?php tribe_events_the_month_day_classes() ?>">
						<?php tribe_get_template_part( 'pro/widgets/mini-calendar/single-day' ) ?>
				</td>
			<?php endwhile; ?>
			</tr>
		</tbody>
	</table>
</div> <!-- .tribe-mini-calendar-grid-wrapper -->
