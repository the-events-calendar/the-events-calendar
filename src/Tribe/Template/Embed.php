<?php
/**
 * @for     Single Event embed template
 * This file contains the hook logic required to create an effective embed view
 *
 * @package TribeEventsCalendar
 *
 */
/**
 * Single event template class
 */
class Tribe__Events__Template__Embed extends Tribe__Events__Template_Factory {

	protected $body_class = 'events-embed';

	public function hooks() {
		parent::hooks();

		add_action( 'embed_head', [ $this, 'embed_head' ] );
	}

	/**
	 * Adds content to the embed head tag
	 *
	 * The embed header DOES NOT have wp_head() executed inside of it. Instead, any scripts/styles
	 * are explicitly output
	 */
	public function embed_head() {
		$css_path = Tribe__Events__Main::instance()->plugin_url . 'src/resources/css/tribe-events-embed.min.css';
		$css_path = add_query_arg( 'ver', Tribe__Events__Main::VERSION, $css_path );
		?>
		<link rel="stylesheet" id="tribe-events-embed-css" href="<?php echo esc_url( $css_path ); ?>" type="text/css" media="all">
		<?php
	}

	/**
	 * Set up the notices for this template
	 **/
	public function set_notices() {
		parent::set_notices();
		$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();

		global $post;

		// Check if event has passed
		$gmt_offset = ( get_option( 'gmt_offset' ) >= '0' ) ? ' +' . get_option( 'gmt_offset' ) : ' ' . get_option( 'gmt_offset' );
		$gmt_offset = str_replace( [ '.25', '.5', '.75' ], [ ':15', ':30', ':45' ], $gmt_offset );

		if ( ! tribe_is_showing_all() && strtotime( tribe_get_end_date( $post, false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) {
			Tribe__Notices::set_notice( 'event-past', sprintf( esc_html__( 'This %s has passed.', 'the-events-calendar' ), $events_label_singular_lowercase ) );
		}
	}
}
