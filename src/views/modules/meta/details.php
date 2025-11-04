<?php
/**
 * Single Event Meta (Details) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.11
 *
 * @since 4.6.19
 * @since 6.15.11 Replaced definition list markup with unordered list for improved accessibility.
 *
 * @package TribeEventsCalendar
 */


$event_id             = Tribe__Main::post_id_helper();
$time_format          = get_option( 'time_format', Tribe__Date_Utils::TIMEFORMAT );
$time_range_separator = tec_events_get_time_range_separator();
$show_time_zone       = tribe_get_option( 'tribe_events_timezones_show_zone', false );
$local_start_time     = tribe_get_start_date( $event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
$time_zone_label      = Tribe__Events__Timezones::is_mode( 'site' ) ? Tribe__Events__Timezones::wp_timezone_abbr( $local_start_time ) : Tribe__Events__Timezones::get_event_timezone_abbr( $event_id );

$start_datetime = tribe_get_start_date();
$start_date = tribe_get_start_date( null, false );
$start_time = tribe_get_start_date( null, false, $time_format );
$start_ts = tribe_get_start_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );

$end_datetime = tribe_get_end_date();
$end_date = tribe_get_display_end_date( null, false );
$end_time = tribe_get_end_date( null, false, $time_format );
$end_ts = tribe_get_end_date( null, false, Tribe__Date_Utils::DBDATEFORMAT );

$time_formatted = null;
if ( $start_time == $end_time ) {
	$time_formatted = esc_html( $start_time );
} else {
	$time_formatted = esc_html( $start_time . $time_range_separator . $end_time );
}

/**
 * Returns a formatted time for a single event
 *
 * @var string Formatted time string
 * @var int Event post id
 */
$time_formatted = apply_filters( 'tribe_events_single_event_time_formatted', $time_formatted, $event_id );

/**
 * Returns the title of the "Time" section of event details
 *
 * @var string Time title
 * @var int Event post id
 */
$time_title = apply_filters( 'tribe_events_single_event_time_title', __( 'Time:', 'the-events-calendar' ), $event_id );

$cost    = tribe_get_formatted_cost();
$website = tribe_get_event_website_link( $event_id );
$website_title = tribe_events_get_event_website_title();
?>

<div class="tribe-events-meta-group tribe-events-meta-group-details">
	<h2 class="tribe-events-single-section-title"> <?php esc_html_e( 'Details', 'the-events-calendar' ); ?> </h2>
	<ul class="tribe-events-meta-list">

		<?php
		do_action( 'tribe_events_single_meta_details_section_start' );

		// All day (multiday) events
		if ( tribe_event_is_all_day() && tribe_event_is_multiday() ) :
			?>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-start-date-label tribe-events-meta-label"><?php esc_html_e( 'Start:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-start-date published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>
				</span>
			</li>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-end-date-label tribe-events-meta-label"><?php esc_html_e( 'End:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-end-date dtend" title="<?php echo esc_attr( $end_ts ); ?>"> <?php echo esc_html( $end_date ); ?> </abbr>
				</span>
			</li>

		<?php
		// All day (single day) events
		elseif ( tribe_event_is_all_day() ):
			?>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-start-date-label tribe-events-meta-label"><?php esc_html_e( 'Date:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-start-date published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>
				</span>
			</li>

		<?php
		// Multiday events
		elseif ( tribe_event_is_multiday() ) :
			?>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-start-datetime-label tribe-events-meta-label"><?php esc_html_e( 'Start:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-start-datetime updated published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_datetime ); ?> </abbr>
					<?php if ( $show_time_zone ) : ?>
						<span class="tribe-events-abbr tribe-events-time-zone published "><?php echo esc_html( $time_zone_label ); ?></span>
					<?php endif; ?>
				</span>
			</li>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-end-datetime-label tribe-events-meta-label"><?php esc_html_e( 'End:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-end-datetime dtend" title="<?php echo esc_attr( $end_ts ); ?>"> <?php echo esc_html( $end_datetime ); ?> </abbr>
					<?php if ( $show_time_zone ) : ?>
						<span class="tribe-events-abbr tribe-events-time-zone published "><?php echo esc_html( $time_zone_label ); ?></span>
					<?php endif; ?>
				</span>
			</li>

		<?php
		// Single day events
		else :
			?>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-start-date-label tribe-events-meta-label"><?php esc_html_e( 'Date:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-meta-value">
					<abbr class="tribe-events-abbr tribe-events-start-date published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>
				</span>
			</li>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-start-time-label tribe-events-meta-label"><?php echo esc_html( $time_title ); ?></span>
				<span class="tribe-events-meta-value">
					<div class="tribe-events-abbr tribe-events-start-time published dtstart" title="<?php echo esc_attr( $end_ts ); ?>">
						<?php echo $time_formatted; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
						<?php if ( $show_time_zone ) : ?>
							<span class="tribe-events-abbr tribe-events-time-zone published "><?php echo esc_html( $time_zone_label ); ?></span>
						<?php endif; ?>
					</div>
				</span>
			</li>

		<?php endif ?>

		<?php
		/**
		 * Included an action where we inject Series information about the event.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tribe_events_single_meta_details_section_after_datetime' );
		?>

		<?php
		// Event Cost
		if ( ! empty( $cost ) ) : ?>

			<li class="tribe-events-meta-item">
				<span class="tribe-events-event-cost-label tribe-events-meta-label"><?php esc_html_e( 'Cost:', 'the-events-calendar' ); ?></span>
				<span class="tribe-events-event-cost tribe-events-meta-value"> <?php echo esc_html( $cost ); ?> </span>
			</li>
		<?php endif ?>

		<?php
		echo tribe_get_event_categories(
			get_the_id(),
			[
				'before'       => '',
				'sep'          => ', ',
				'after'        => '',
				'label'        => null, // An appropriate plural/singular label will be provided
				'label_before' => '<li class="tribe-events-meta-item"><span class="tribe-events-event-categories-label tribe-events-meta-label">',
				'label_after'  => '</span>',
				'wrap_before'  => '<span class="tribe-events-event-categories tribe-events-meta-value">',
				'wrap_after'   => '</span></li>',
			]
		);
		?>

		<?php
		tribe_meta_event_archive_tags(
			/* Translators: %s: Event (singular) */
			sprintf(
				esc_html__( '%s Tags:', 'the-events-calendar' ),
				tribe_get_event_label_singular()
			),
			', ',
			true
		);
		?>

		<?php
		// Event Website
		if ( ! empty( $website ) ) : ?>
			<li class="tribe-events-meta-item">
				<?php if ( ! empty( $website_title ) ) : ?>
					<span class="tribe-events-event-url-label tribe-events-meta-label"><?php echo esc_html( $website_title ); ?></span>
				<?php endif; ?>
				<span class="tribe-events-event-url tribe-events-meta-value"> <?php echo $website; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?> </span>
			</li>
		<?php endif ?>

		<?php do_action( 'tribe_events_single_meta_details_section_end' ); ?>
	</ul>
</div>
