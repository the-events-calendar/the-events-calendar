<?php
/**
 * Block: Event Date Time
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-datetime.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7
 *
 */

$event_id = get_the_ID();
$event = get_post( $event_id );

/**
 * If a yearless date format should be preferred.
 *
 * By default, this will be true if the event starts and ends in the current year.
 *
 * @since 0.2.5-alpha
 *
 * @param bool    $use_yearless_format
 * @param WP_Post $event
 */
$use_yearless_format = apply_filters( 'tribe_events_event_block_datetime_use_yearless_format',
	(
		tribe_get_start_date( $event_id, false, 'Y' ) === date_i18n( 'Y' )
		&& tribe_get_end_date( $event_id, false, 'Y' ) === date_i18n( 'Y' )
	),
	$event
);

$time_format = tribe_get_time_format();
$date_format = tribe_get_date_format( $use_yearless_format );

$timezone = get_post_meta( $event_id, '_EventTimezone', true );
$show_time_zone = $this->attr( 'showTimeZone' );
$time_zone_label = $this->attr( 'timeZoneLabel' );


$formatted_start_date = tribe_get_start_date( $event_id, false, $date_format );
$formatted_start_time = tribe_get_start_time( $event_id, $time_format );
$formatted_end_date = tribe_get_end_date( $event_id, false, $date_format );
$formatted_end_time = tribe_get_end_time( $event_id, $time_format );

$separator_date = get_post_meta( $event_id, '_EventDateTimeSeparator', true );
$separator_time = get_post_meta( $event_id, '_EventTimeRangeSeparator', true );

if ( empty( $separator_time ) ) {
	$separator_time = tribe_get_option( 'timeRangeSeparator', ' - ' );
}
if ( empty( $separator_date ) ) {
	$separator_date = tribe_get_option( 'dateTimeSeparator', ' - ' );
}

$is_all_day       = tribe_event_is_all_day( $event_id );
$is_same_day      = $formatted_start_date == $formatted_end_date;

?>

<?php $event_id = $this->get( 'post_id' ); ?>
<div class="tribe-events-schedule tribe-clearfix">
	<h2 class="tribe-events-schedule__datetime">
		<span class="tribe-events-schedule__date tribe-events-schedule__date--start">
			<?php echo $formatted_start_date; ?>
		</span>

		<?php if ( ! $is_all_day ) : ?>
			<span class="tribe-events-schedule__separator tribe-events-schedule__separator--date">
				<?php echo $separator_date; ?>
			</span>
			<span class="tribe-events-schedule__time tribe-events-schedule__time--start">
				<?php echo $formatted_start_time; ?>
			</span>
		<?php elseif ( $is_same_day ) : ?>
			<span class="tribe-events-schedule__all-day"><?php echo __( 'All day', 'the-events-calendar' ); ?></span>
		<?php endif; ?>

		<?php if ( ! $is_all_day || ! $is_same_day ) : ?>
			<span class="tribe-events-schedule__separator tribe-events-schedule__separator--time">
				<?php echo $separator_time; ?>
			</span>
		<?php endif; ?>

		<?php if ( ! $is_same_day ) : ?>
			<span class="tribe-events-schedule__date tribe-events-schedule__date--end">
				<?php echo $formatted_end_date; ?>
			</span>

			<?php if ( ! $is_all_day ) : ?>
				<span class="tribe-events-schedule__separator tribe-events-schedule__separator--date">
					<?php echo $separator_date; ?>
				</span>
				<span class="tribe-events-schedule__time tribe-events-schedule__time--end">
					<?php echo $formatted_end_time; ?>
				</span>
			<?php endif; ?>

		<?php elseif ( ! $is_all_day ) : ?>
			<span class="tribe-events-schedule__time tribe-events-schedule__time--end">
				<?php echo $formatted_end_time; ?>
			</span>
		<?php endif; ?>

		<?php if ( $show_time_zone ) : ?>
			<span class="tribe-events-schedule__timezone"><?php echo esc_html( $time_zone_label ); ?></span>
		<?php endif; ?>
	</h2>
</div>
