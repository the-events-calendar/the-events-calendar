<?php
/**
 * Implements a shortcode that wraps the existing featured venue widget. Basic usage
 * is as follows (using a venue's post ID):
 *
 *     [tribe_featured_venue id="123"]
 *
 * Besides supplying the venue ID, a slug can be used. It is also possible to limit
 * the number of upcoming events:
 *
 *     [tribe_featured_venue slug="the-club" count="5"]
 *
 * A title can also be added if desired:
 *
 *     [tribe_featured_venue slug="busy-location" title="Check out these events!"]
 */
class Tribe__Events__Pro__Shortcodes__Featured_Venue {
	/**
	 * Default arguments expected by the featured venue widget.
	 *
	 * @var array
	 */
	protected $default_args = array(
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		'slug'           => '',
		'venue'          => '',
		'id'             => '',
		'count'          => '',
	);

	protected $arguments = array();

	public function __construct() {
		add_shortcode( 'tribe_featured_venue', array( $this, 'do_shortcode' ) );
	}

	public function do_shortcode( $attributes ) {
		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->parse_args();

		// If no venue has been set simply bail with an empty string
		if ( ! isset( $this->arguments['venue_ID'] ) ) {
			return '';
		}

		ob_start();
		// We use $this->arguments for both the args and the instance vars here
		the_widget( 'TribeVenueWidget', $this->arguments, $this->arguments );
		return ob_get_clean();
	}

	/**
	 * Venue can be specified with one of "id" or "venue". Limit can be set using a
	 * "count" attribute.
	 */
	protected function parse_args() {
		if ( ! empty( $this->arguments['id'] ) ) {
			$this->arguments['venue_ID'] = (int) $this->arguments['venue'];
		} elseif ( ! empty( $this->arguments['venue'] ) ) {
			$this->arguments['venue_ID'] = (int) $this->arguments['venue'];
		} elseif ( ! empty( $this->arguments['slug'] ) ) {
			$this->set_by_slug();
		}

		if ( ! empty( $this->arguments['count'] ) ) {
			$this->arguments['posts_per_page'] = (int) $this->arguments['count'];
		} else {
			$this->arguments['posts_per_page'] = (int) tribe_get_option( 'postsPerPage', 10 );
		}
	}

	/**
	 * Facilitates specifying the venue by providing its slug.
	 */
	protected function set_by_slug() {
		$venues = get_posts( array(
			'post_type' => TribeEvents::VENUE_POST_TYPE,
			'name' => $this->arguments['slug'],
			'posts_per_page' => 1
		) );

		if ( empty( $venues ) ) {
			return;
		}

		$venue = array_shift( $venues );
		$this->arguments['venue_ID'] = (int) $venue->ID;
	}
}