<?php
/**
 * Single Event Template for Widgets
 *
 * This template is used to render single events for both the calendar and advanced
 * list widgets, facilitating a common appearance for each as standard.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe-events/pro/widgets/modules/single-event.php
 *
 * @package TribeEventsCalendarPro
 *
 */

$mini_cal_event_atts = tribe_events_get_widget_event_atts();

$postDate = tribe_events_get_widget_event_post_date();

$organizer_ids = tribe_get_organizer_ids();
$multiple_organizers = count( $organizer_ids ) > 1;
?>

<div class="tribe-mini-calendar-event event-<?php esc_attr_e( $mini_cal_event_atts['current_post'] ); ?> <?php esc_attr_e( $mini_cal_event_atts['class'] ); ?>">
	<div class="list-date">
		<span
			class="list-dayname"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_dayname', date_i18n( 'D', $postDate ), $postDate, $mini_cal_event_atts['class'] ); ?></span>
		<span
			class="list-daynumber"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_daynumber', date_i18n( 'd', $postDate ), $postDate, $mini_cal_event_atts['class'] ); ?></span>
	</div>

	<div class="list-info">
		<?php do_action( 'tribe_events_list_widget_before_the_event_title' ); ?>

		<h2 class="tribe-events-title">
			<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h2>

		<?php do_action( 'tribe_events_list_widget_after_the_event_title' ); ?>

		<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>

		<div class="tribe-events-duration">
			<?php echo tribe_events_event_schedule_details(); ?>
		</div>

		<?php if ( isset( $cost ) && $cost && tribe_get_cost() != '' ) : ?>
			<span class="tribe-events-divider">|</span>
			<div class="tribe-events-event-cost">
				<?php echo tribe_get_cost( null, true ); ?>
			</div>
		<?php endif ?>

		<div class="tribe-events-location">

			<?php if ( isset( $venue ) && $venue && tribe_get_venue() != '' ): ?>
				<span class="tribe-events-venue"><?php echo tribe_get_venue_link(); ?></span>
			<?php endif ?>

			<?php if ( isset( $address ) && $address && tribe_get_address() != '' ): ?>
				<span class="tribe-street-address"><?php echo tribe_get_address(); ?></span>
			<?php endif ?>

			<?php if ( isset( $city ) && $city && tribe_get_city() != '' ): ?>
				<span class="tribe-events-locality"><?php echo tribe_get_city(); ?></span>
			<?php endif ?>

			<?php if ( isset( $region ) && $region && tribe_get_region() != '' ): ?>
				<span class="tribe-events-region"><?php echo tribe_get_region(); ?></span>
			<?php endif ?>

			<?php if ( isset( $zip ) && $zip && tribe_get_zip() != '' ): ?>
				<span class="tribe-events-postal-code"><?php echo tribe_get_zip(); ?></span>
			<?php endif ?>

			<?php if ( isset( $country ) && $country && tribe_get_country() != '' ): ?>
				<span class="tribe-country-name"><?php echo tribe_get_country(); ?></span>
			<?php endif ?>

			<?php if ( isset( $organizer ) && $organizer && ! empty( $organizer_ids ) ): ?>
				<span class="tribe-events-organizer">
					<?php echo tribe_get_organizer_label( ! $multiple_organizers ); ?>:
					<?php
					$organizer_links = array();
					foreach ( $organizer_ids as $organizer_id ) {
						if ( ! $organizer_id ) {
							continue;
						}

						$organizer_links[] = tribe_get_organizer_link( $organizer_id, true );
					}// end foreach

					$and = _x( 'and', 'list separator for final two elements', 'tribe-events-calendar-pro' );
					if ( 1 == count( $organizer_links ) ) {
						echo $organizer_links[0];
					}// end if
					elseif ( 2 == count( $organizer_links ) ) {
						echo $organizer_links[0] . ' ' . esc_html( $and ) . ' ' . $organizer_links[1];
					}// end elseif
					else {
						$last_organizer = array_pop( $organizer_links );

						echo implode( ', ', $organizer_links );
						echo esc_html( ', ' . $and . ' ' );
						echo $last_organizer;
					}// end else
					?>
				</span>
			<?php endif ?>

			<?php if ( isset( $phone ) && $phone && tribe_get_phone() != '' ): ?>
				<span class="tribe-events-tel"><?php echo tribe_get_phone(); ?></span>
			<?php endif ?>

		</div>

		<?php do_action( 'tribe_events_list_widget_after_the_meta' ) ?>

	</div> <!-- .list-info -->
</div>
