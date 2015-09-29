<?php
/**
 * Implements a shortcode that wraps the existing this week widget. Basic usage
 * is as follows:
 *
 *     [tribe_this_week]
 *
 * Besides supplying the venue ID, a slug can be used. It is also possible to limit
 * the number of upcoming events:
 *
 *     [tribe_this_week limit="5"]
 *
 * A title can also be added if desired:
 *
 *     [tribe_this_week title="Check out these events!"]
 */
class Tribe__Events__Pro__Shortcodes__This_Week extends Tribe__Events__Pro__Shortcodes__Filtered_Shortcode {

	public $output = '';

	/**
	* Shortcode Instance Counter to use for Unique ID on Wrapper
	*
	* @var int
	*/
	public static $counter = 0;

	/**
	 * The shortcode allows filtering by event categories and by post tags,
	 * in line with what the calendar widget itself supports.
	 *
	 * @var array
	 */
	protected $tax_relationships = array(
		'categories' => Tribe__Events__Main::TAXONOMY,
		'tags'       => 'post_tag',
	);


	/**
	* Default arguments expected by the featured venue widget.
	*
	* @var array
	*/
	protected $default_args = array(
		// This Week Properties
		'layout'          => 'horizontal',
		'highlight_color' => '',
		'count'           => 3,
		'widget_id'       => '',
		// Taxonomy properties
		'category'        => '',
		'categories'      => '',
		'operand'         => 'OR',
		'tag'             => '',
		'tags'            => '',
		// Date Properties
		'start_date'      => '',
		'week_offset'     => '',
		'hide_weekends'   => false,
		// General widget properties
		'before_widget'   => '',
		'before_title'    => '',
		'title'           => '',
		'after_title'     => '',
		'after_widget'    => '',
	);

	protected $arguments = array();


	public function __construct( $attributes ) {

		//Shortcode Counter
		self::$counter ++;

		//Set ID and unique css ID for wrapper
		$this->default_args['widget_id']     = self::$counter;
		$this->default_args['before_widget'] = '<div id="tribe-this-week-events-widget-100' . self::$counter . '" class="tribe-this-week-events-widget" >';
		$this->default_args['after_widget']  = '</div>';

		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->taxonomy_filters();

		ob_start();
		// We use $this->arguments for both the args and the instance vars here
		the_widget( 'Tribe__Events__Pro__This_Week_Widget', $this->arguments, $this->arguments );
		$this->output = ob_get_clean();
	}

	}
