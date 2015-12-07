<?php
/**
 * The Events Calendar integration with Event Tickets Attendees Report class
 *
 * @package The Events Calendar
 * @subpackage Event Tickets
 * @since 4.0.1
 */
class Tribe__Events__Event_Tickets__Attendees_Report {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds hooks for injecting/overriding aspects of the Attendees Report from Event Tickets
	 *
	 * @since 4.0.1
	 */
	public function add_hooks() {
		add_action( 'tribe_tickets_attendees_event_details_list_top', array( $this, 'event_details_top' ) );
	}

	/**
	 * Injects event meta data into the Attendees report
	 */
	public function event_details_top( $event_id ) {
		if ( Tribe__Events__Main::POSTTYPE !== get_post_type( $event_id ) ) {
			return;
		}

		$url = null;
		if ( tribe_has_venue( $event_id ) ) {
			$venue_id = tribe_get_venue_id( $event_id );

			$url = get_post_meta( $venue_id, '_VenueURL', true );
			if ( $url ) {
				$url_path = @parse_url( $url, PHP_URL_PATH );
				$display_url = @parse_url( $url, PHP_URL_HOST );
				$display_url .= empty( $url_path ) && $url_path !== '/' ? '/&hellip;' : '';
				$display_url = apply_filters( 'tribe_venue_display_url', $display_url, $url, $venue_id );
			}
		}

		?>
		<li>
			<strong><?php esc_html_e( 'Start Date / Time:', 'event-tickets' ) ?></strong>
			<?php echo tribe_get_start_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>
		</li>

		<li>
			<strong><?php esc_html_e( 'End Date / Time:', 'event-tickets' ) ?></strong>
			<?php echo tribe_get_end_date( $event_id, false, tribe_get_datetime_format( true ) ); ?>
		</li>
		<?php

		if ( tribe_has_venue( $event_id ) ) {
			?>

			<li class="venue-name">
				<strong><?php echo tribe_get_venue_label_singular(); ?>: </strong>
				<a href="<?php echo get_edit_post_link( $venue_id ); ?>" title="<?php esc_html_e( 'Edit Venue', 'the-events-calendar' ); ?>"><?php echo tribe_get_venue( $event_id ) ?></a>
			</li>

			<li class="venue-address">
				<strong><?php _e( 'Address:', 'the-events-calendar' ); ?> </strong>
				<?php echo tribe_get_full_address( $venue_id ); ?>
			</li>

			<?php
			if ( $phone = tribe_get_phone( $venue_id ) ) {
				?>
				<li class="venue-phone">
					<strong><?php echo esc_html( __( 'Phone:', 'the-events-calendar' ) ); ?> </strong>
					<?php echo esc_html( $phone ); ?>
				</li>
				<?php
			}//end if

			if ( $url ) {
				?>
				<li class="venue-url">
					<strong><?php echo esc_html( __( 'Website:', 'the-events-calendar' ) ); ?> </strong>
					<a target="_blank" href="<?php echo esc_url( $url ); ?>">
						<?php echo esc_html( $display_url ); ?>
					</a>
				</li>
				<?php
			}//end if
		}
	}
}
