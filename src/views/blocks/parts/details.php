<?php
/**
 * Details template part for the Block Classic Event Details
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/details.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
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

$cost = tribe_get_formatted_cost();
$website = tribe_get_event_website_link( $event_id );
?>

<div class="tribe-events-meta-group tribe-events-meta-group-details">
	<h3 class="tribe-events-single-section-title">
		<?php if ( empty( $attributes['detailsTitle'] ) ) : ?>
			<?php esc_html_e( 'Details', 'the-events-calendar' ); ?>
		<?php else : ?>
			<?php echo is_array( $attributes['detailsTitle'] ) ? esc_html( reset( $attributes['detailsTitle'] ) ) : esc_html( $attributes['detailsTitle'] ); ?>
		<?php endif; ?>

	</h3>
	<dl>

		<?php
		do_action( 'tribe_events_single_meta_details_section_start' );

		// All day (multiday) events
		if ( tribe_event_is_all_day() && tribe_event_is_multiday() ) :
			?>

			<dt> <?php esc_html_e( 'Start:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr tribe-events-start-datetime published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>

			</dd>

			<dt> <?php esc_html_e( 'End:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr dtend" title="<?php echo esc_attr( $end_ts ); ?>"> <?php echo esc_html( $end_date ); ?> </abbr>
			</dd>

		<?php
		// All day (single day) events
		elseif ( tribe_event_is_all_day() ):
			?>

			<dt> <?php esc_html_e( 'Date:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr tribe-events-start-datetime published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>
			</dd>

		<?php
		// Multiday events
		elseif ( tribe_event_is_multiday() ) :
			?>

			<dt> <?php esc_html_e( 'Start:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr updated published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_datetime ); ?> </abbr>
				<?php if ( $show_time_zone ) : ?>
					<span class="tribe-events-abbr tribe-events-time-zone published"><?php echo esc_html( $time_zone_label ); ?></span>
				<?php endif; ?>
			</dd>

			<dt> <?php esc_html_e( 'End:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr dtend" title="<?php echo esc_attr( $end_ts ); ?>"> <?php echo esc_html( $end_datetime ); ?> </abbr>
				<?php if ( $show_time_zone ) : ?>
					<span class="tribe-events-abbr tribe-events-time-zone published"><?php echo esc_html( $time_zone_label ); ?></span>
				<?php endif; ?>
			</dd>

		<?php
		// Single day events
		else :
			?>

			<dt> <?php esc_html_e( 'Date:', 'the-events-calendar' ); ?> </dt>
			<dd>
				<abbr class="tribe-events-abbr tribe-events-start-date published dtstart" title="<?php echo esc_attr( $start_ts ); ?>"> <?php echo esc_html( $start_date ); ?> </abbr>
			</dd>

			<dt> <?php echo esc_html( $time_title ); ?> </dt>
			<dd>
				<div class="tribe-events-abbr tribe-events-start-time published dtstart" title="<?php echo esc_attr( $end_ts ); ?>">
					<?php echo $time_formatted; ?>
					<?php if ( $show_time_zone ) : ?>
						<span class="tribe-events-abbr tribe-events-time-zone published"><?php echo esc_html( $time_zone_label ); ?></span>
					<?php endif; ?>
				</div>
			</dd>

		<?php endif ?>

		<?php
		// Event Cost
		if ( ! empty( $cost ) ) : ?>
			<dt> <?php esc_html_e( 'Cost:', 'the-events-calendar' ); ?> </dt>
			<dd class="tribe-events-event-cost"> <?php echo esc_html( $cost ); ?> </dd>
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
		echo tribe_get_event_categories(
			get_the_id(),
			[
				'before'       => '',
				'sep'          => ', ',
				'after'        => '',
				'label'        => null, // An appropriate plural/singular label will be provided
				'label_before' => '<dt class="tribe-events-event-categories-label">',
				'label_after'  => '</dt>',
				'wrap_before'  => '<dd class="tribe-events-event-categories">',
				'wrap_after'   => '</dd>',
			]
		);
		?>

		<?php
		/* Translators: %s: Event (singular) */
		tribe_meta_event_archive_tags( sprintf( esc_html__( '%s Tags:', 'the-events-calendar' ), tribe_get_event_label_singular() ), ', ', true );
		?>

		<?php
		// Event Website
		if ( ! empty( $website ) ) : ?>

			<dt> <?php esc_html_e( 'Website:', 'the-events-calendar' ); ?> </dt>
			<dd class="tribe-events-event-url"> <?php echo $website; ?> </dd>
		<?php endif ?>

		<?php do_action( 'tribe_events_single_meta_details_section_end' ); ?>
	</dl>
</div>
