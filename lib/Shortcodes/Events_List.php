<?php

/**
 * Implements a shortcode that wraps the existing advanced events list widget.
 *
 * Basic usage is as follows:
 *
 *     [tribe_events_list]
 *
 * Slightly more advanced usage, demonstrating tag and category filtering, is as follows:
 *
 *     [tribe_events_list tag="black-swan-event, #20, #60" categories="twist,samba, #491, groove"]
 *
 * Note that slugs and numeric IDs are both acceptable within comma separated lists of terms
 * but IDs must be prefixed with a # symbol (this is because a number-only slug is possible, so
 * we need to be able to differentiate between them).
 *
 * You can also control the amount of information that is displayed per event (just as you might
 * if configuring the advanced list widget through its normal UI). For example, to include the
 * venue city and organizer details, you could do:
 *
 *     [tribe_events_list city="1" organizer="1"]
 *
 * List of optional information attributes:
 *
 *     address, city, cost, country, organizer, phone, region, venue, zip
 *
 */
class Tribe__Events__Pro__Shortcodes__Events_List {
	/**
	 * The shortcode allows filtering by event categories and by post tags,
	 * in line with what the calendar widget itself supports.
	 *
	 * @var array
	 */
	protected $tax_relationships = array(
		'categories' => TribeEvents::TAXONOMY,
		'tags' => 'post_tag'
	);

	/**
	 * Default arguments expected by the calendar widget.
	 *
	 * @var array
	 */
	protected $default_args = array(
		// General widget properties
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		// Taxonomy properties
		'tag'        => '',
		'tags'       => '',
		'category'   => '',
		'categories' => '',

		// Events to show
		'limit'              => '',
		'no_upcoming_events' => '',

		// Optional additional information to include per event
		'venue'     => '',
		'country'   => '',
		'address'   => '',
		'city'      => '',
		'region'    => '',
		'zip'       => '',
		'phone'     => '',
		'cost'      => '',
		'organizer' => ''
	);

	protected $arguments = array();
	protected $filters = array();
	protected $terms = array();


	public function __construct() {
		add_shortcode( 'tribe_events_list', array( $this, 'do_shortcode' ) );
	}

	public function do_shortcode( $attributes ) {
		$this->reset();
		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->taxonomy_filters();

		ob_start();
		the_widget( 'TribeEventsAdvancedListWidget', $this->arguments, $this->arguments );
		return ob_get_clean();
	}

	protected function reset() {
		$this->filters = array();
		$this->terms = array();
	}

	/**
	 * Sets up an array of taxonomy filters, if required by the shortcode
	 * arguments.
	 */
	protected function taxonomy_filters() {
		// Consolidate plural/singular forms into one
		$params  = array();
		$params['categories'] = $this->arguments['categories'] . ',' . $this->arguments['category'];
		$params['tags'] = $this->arguments['tags'] . ',' . $this->arguments['tag'];

		// Build our taxonomy filter
		foreach ( $this->tax_relationships as $param => $tax ) {
			// Check for taxonomy terms for each supported taxonomy
			$this->terms = explode( ',', $params[$param] );
			foreach ( $this->terms as $term ) {
				$this->add_term( $term, $tax );
			}
		}

		// Add the filters to the list of widget arguments
		if ( ! empty( $this->filters ) ) $this->arguments['raw_filters'] = $this->filters;
	}

	/**
	 * Potentially add a taxonomy term to our list of filters.
	 *
	 * @param $term
	 * @param $tax
	 */
	protected function add_term( $term, $tax ) {
		$term = trim( $term );
		if ( empty( $term ) ) return;

		// Accept term IDs - these should be prefixed with a # symbol
		if ( 0 === strpos( $term, '#' ) && is_numeric( substr( $term, 1 ) ) ) {
			$this->filters[$tax][] = absint( substr( $term, 1 ) );
		}
		// Also accept term slugs...
		else {
			$term_obj = get_term_by( 'slug', $term, $tax );
			if ( false === $term_obj ) return;
			$this->filters[$tax][] = $term_obj->term_id;
		}
	}
}