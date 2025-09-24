<?php
/**
 * View: List Single Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 6.2.0 Added the `tec_events_view_venue_after_address` action.
 * @since 6.15.3 Added post password protection.
 *
 * @version 6.15.3
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 * @var string  $slug  The slug of the view.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( ! $event->venues->count() ) {
	return;
}

$separator            = esc_html_x( ', ', 'Address separator', 'the-events-calendar' );
$venue                = $event->venues[0];
$append_after_address = array_filter( array_map( 'trim', [ $venue->state_province, $venue->state, $venue->province ] ) );
$address              = $venue->address . ( $venue->address && ( $append_after_address || $venue->city ) ? $separator : '' );
?>
<address class="tribe-events-calendar-list__event-venue tribe-common-b2">
	<span class="tribe-events-calendar-list__event-venue-title tribe-common-b2--bold">
		<?php echo wp_kses_post( $venue->post_title ); ?>
	</span>
	<span class="tribe-events-calendar-list__event-venue-address">
		<?php
		if ( ! post_password_required( $venue->ID ) ) {
			echo esc_html( $address );

			if ( ! empty( $venue->city ) ) :
				echo esc_html( $venue->city );
				if ( $append_after_address ) :
					echo $separator;
				endif;
			endif;

			if ( $append_after_address ) :
				echo esc_html( reset( $append_after_address ) );
			endif;

			if ( ! empty( $venue->country ) ) :
				echo $separator . esc_html( $venue->country );
			endif;
		}
		?>
	</span>
	<?php
	/**
	 * Fires after the full venue has been displayed.
	 *
	 * @since 6.2.0
	 *
	 * @param WP_Post $event Event post object.
	 * @param string  $slug  Slug of the view.
	 */
	do_action( 'tec_events_view_venue_after_address', $event, $slug );
	?>
</address>
