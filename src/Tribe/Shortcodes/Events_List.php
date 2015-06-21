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
class Tribe__Events__Pro__Shortcodes__Events_List extends Tribe__Events__Pro__Shortcodes__Filtered_Shortcode {
	public $output = '';

	/**
	 * The shortcode allows filtering by event categories and by post tags,
	 * in line with what the calendar widget itself supports.
	 *
	 * @var array
	 */
	protected $tax_relationships = array(
		'categories' => Tribe__Events__Main::TAXONOMY,
		'tags' => 'post_tag',
	);

	/**
	 * Default arguments expected by the advanced list widget.
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
		'organizer' => '',
	);

	protected $arguments = array();


	public function __construct( $attributes ) {
		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->taxonomy_filters();
		Tribe__Events__Pro__Widgets::enqueue_calendar_widget_styles();

		ob_start();
		the_widget( 'Tribe__Events__Pro__Advanced_List_Widget', $this->arguments, $this->arguments );
		$this->output = ob_get_clean();
	}
}
