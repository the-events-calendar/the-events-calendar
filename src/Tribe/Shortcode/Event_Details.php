<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Setup the Event Details Shortcode to be able to place the Details for a Event on other pages
 *
 * @since 4.1
 */
class Tribe__Events__Shortcode__Event_Details {

	/**
	 * Static Singleton Factory Method
	 *
	 * @since  4.1
	 * @return Tribe__Events__Shortcode__Event_Details
	 */
	public static function instance() {
		return tribe( 'tec.shortcodes.event-details' );
	}

	/**
	 * Add the necessary hooks as the correct moment in WordPress
	 *
	 * @since  4.1
	 * @return  void
	 */
	public function hook() {
		add_action( 'init', [ $this, 'add_shortcode' ] );
	}

	/**
	 * Static private variable holding this Shortcode Namespace, which should always be "tribe"
	 *
	 * @since 4.1
	 * @var string
	 */
	private $nspace = 'tribe';

	/**
	 * Static private variable holding this Shortcode Slug
	 *
	 * @since 4.1
	 * @var string
	 */
	private $slug = 'event-details';

	/**
	 * Creates the Shortcode tag based on the static variables $nspace and $slug
	 *
	 * @since 4.1
	 * @return string
	 */
	public function get_shortcode_tag() {
		/**
		 * Use this hook to filter the tribe namespace on shortcodes
		 *
		 * @since 4.1
		 *
		 * @param string Namespace
		 * @param string Static Class Name
		 * @param object Instance of this class
		 */
		$nspace = apply_filters( 'tribe_events_shortcode_namespace', $this->nspace, __CLASS__, $this );

		// Fetch the Slug
		$slug = $this->get_shortcode_slug();

		$tag = sanitize_title_with_dashes( $nspace ) . ':' . sanitize_title_with_dashes( $slug );

		/**
		 * Use this hook to filter the final tag of the Shortcode
		 *
		 * @since 4.1
		 *
		 * @param string The complete Tag
		 * @param string Static Class Name
		 * @param object Instance of this class
		 */
		$tag = apply_filters( 'tribe_events_shortcode_tag', $tag, __CLASS__, $this );

		return $tag;
	}

	/**
	 * Gets the Shortcode slug allowing it to be filtered
	 *
	 * @since 4.1
	 * @return string
	 */
	public function get_shortcode_slug() {
		/**
		 * Use this hook to filter the slug of shortcodes
		 *
		 * @since 4.1
		 *
		 * @param string The Slug
		 * @param string Static Class Name
		 * @param object Instance of this class
		 */
		$slug = apply_filters( 'tribe_events_shortcode_slug', $this->slug, __CLASS__, $this );

		return $slug;
	}

	/**
	 * This will be called at hook "init" to allow other plugins and themes to hook to shortcode easily
	 *
	 * @since 4.1
	 * @return void
	 */
	public function add_shortcode() {
		$tag = $this->get_shortcode_tag();

		add_shortcode( $tag, array( $this, 'do_shortcode' ) );
	}

	/**
	 * Actually create the shortcode output
	 *
	 * @since  4.1
	 *
	 * @param  array $args    The Shortcode arguments
	 *
	 * @return string
	 */
	public function do_shortcode( $args ) {
		$tag = $this->get_shortcode_tag();
		$slug = $this->get_shortcode_slug();

		$args = (object) shortcode_atts( array(
			'align' => 'left',
			'id' => null,
		), $args, $tag );

		if ( ! is_null( $args->id ) && is_numeric( $args->id ) ) {
			$event = get_post( $args->id );

			// Then If Event was fetch correctly we set the global
			if ( $event ) {
				global $post;
				// Store the Original we will restore it later
				$original_event = $post;

				// Set the new Event for now
				$post = $event;

				// Use WP to config the Post Data
				setup_postdata( $post );
			}
		}

		// Set the Container Classes
		$classes = array(
			'tribe-shortcode',
			'tribe-events-single-section',
			'tribe-events-event-meta',
			'tribe-clearfix',
		);

		// Add Alignment
		if ( in_array( $args->align, array( 'center', 'left', 'right' ) ) ) {
			$classes[] = 'tribe-shortcode-align-' . $args->align;
		}

		/**
		 * Use this hook to filter the classes for this shortcode container
		 *
		 * @since 4.1
		 *
		 * @param array  Array of classes used on the Container
		 * @param object Arguments set on the shortcode
		 * @param string Shortcode tag
		 */
		$classes = apply_filters( 'tribe_events_shortcode_' . $slug . '_container_classes', $classes, $args, $tag );

		// Ensure the expected CSS is available to style the shortcode output (this will
		// happen automatically in event views, but not elsewhere)
		tribe_asset_enqueue_group( 'events-styles' );

		// Start to record the Output
		ob_start();

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		// Print the Actual HTML
		tribe_get_template_part( 'modules/meta/details' );

		echo '</div>';

		// Save it to a variable
		$html = ob_get_clean();

		if ( isset( $original_event ) ) {
			$post = $original_event;

			// Use WP method to restore it back to original
			setup_postdata( $post );
		}

		return $html;
	}
}
