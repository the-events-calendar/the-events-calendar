<?php
/**
 * Month View Grid Loop
 * This file sets up the structure for the month grid loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/loop-grid.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php
$days_of_week = tribe_events_get_days_of_week();
$week = 0;
global $wp_locale;
?>

<?php do_action( 'tribe_events_before_the_grid' ) ?>
	<table class="tribe-events-calendar">
		<thead>
		<tr>
			<?php foreach ( $days_of_week as $day ) : ?>

				<th id="tribe-events-<?php echo strtolower( $day ) ?>" title="<?php echo $day ?>" data-day-abbr="<?php echo $wp_locale->get_weekday_abbrev( $day ); ?>"><?php echo $day ?></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody class="vcalendar">
		<tr>
			<?php while (tribe_events_have_month_days()) : tribe_events_the_month_day(); ?>
			<?php if ( $week != tribe_events_get_current_week() ) : $week ++; ?>
		</tr>
		<tr>
			<?php endif;
			$daydata = tribe_events_get_current_month_day(); ?>
			<td class="<?php tribe_events_the_month_day_classes() ?>"
				<?php if ( isset( $daydata['daynum'] ) ) { ?>
					data-day="<?php echo $daydata['daynum'] ?>"
					<?php
					//Add Day Name Option for Responsive Header
					if ( $daydata['total_events'] > 0 ) {
						$day_name = tribe_event_format_date( $daydata['date'], false );
						?>
						data-date-name="<?php echo $day_name ?>"
					<?php
					}
					?>

				<?php } ?>
				>
				<?php tribe_get_template_part( 'month/single', 'day' ) ?>
			</td>
			<?php endwhile; ?>
		</tr>
		</tbody>
	</table><!-- .tribe-events-calendar -->
<?php do_action( 'tribe_events_after_the_grid' ) ?>