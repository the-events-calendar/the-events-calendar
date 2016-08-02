<?php
/**
 * Template Factory
 *
 * The parent class for managing the view methods in core and addons
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Template_Factory' ) ) {
	return;
}

class Tribe__Events__Template_Factory extends Tribe__Template_Factory {
	/**
	 * Length for excerpts on this template
	 *
	 * @var int
	 **/
	protected $excerpt_length = 80;

	/**
	 * Text for excerpt more on this template
	 *
	 * @var string
	 **/
	protected $excerpt_more = '&hellip;';

	/**
	 * Body class on this view
	 *
	 * @var string
	 **/
	protected $body_class = '';

	/**
	 * Static variable that holds array of vendor script handles, for adding to later deps.
	 *
	 * @static
	 * @var array
	 */
	protected $comments_off = false;

	/**
	 * Run include packages, set up hooks
	 *
	 **/
	public function __construct() {
		$this->hooks();
		$this->asset_packages();
	}

	/**
	 * Set up hooks for this template
	 *
	 **/
	protected function hooks() {

		$current_class = get_class( $this );
		$ajax_hook     = constant( $current_class . '::AJAX_HOOK' );

		// set up queries, vars, etc that needs to be used in this view
		add_action( 'tribe_events_before_view', array( $this, 'setup_view' ), 10 );

		// ajax requests
		add_action( 'wp_ajax_' . $ajax_hook, array( $this, 'ajax_response' ) );
		add_action( 'wp_ajax_nopriv_' . $ajax_hook, array( $this, 'ajax_response' ) );

		// set notices
		add_action( 'tribe_events_before_view', array( $this, 'set_notices' ), 15 );

		// Don't show the comments form inside the view (if comments are enabled,
		// they'll show on their own after the loop)
		if ( ! ( tribe_get_option( 'tribeEventsTemplate', 'default' ) == '' ) ) {
			add_filter( 'comments_template', array( $this, 'remove_comments_template' ) );
		}

		// Remove the comments template entirely if needed
		add_filter( 'tribe_get_option', array( $this, 'comments_off' ), 10, 2 );

		// cleanup after view (reset query, etc)
		add_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

		// add wrapper html and input hash to non-ajax request
		add_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
		add_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
		add_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

		// hide sensitive event info if post is password protected
		add_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

		// implement a filter for the page title. Support WordPress < 4.4
		add_filter( 'wp_title', array( $this, 'title_tag' ), 10, 2 );

		// implement a filter for the page title. Support WordPress >= 4.4
		add_filter( 'document_title_parts', array( $this, 'title_tag' ) );

		// add body class
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// event classes
		add_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

	}

	/**
	 * Asset calls for vendor packages
	 *
	 * @param string $name
	 * @param array  $deps Dependents
	 */
	public static function asset_package( $name, $deps = array() ) {

		$common = Tribe__Events__Main::instance();
		$prefix = 'tribe-events'; // Tribe__Events__Main::POSTTYPE;

		// setup plugin resources & 3rd party vendor urls
		$vendor_url = trailingslashit( $common->plugin_url ) . 'vendor/';

		self::handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $common );
	}

	/**
	 * Handles an asset package request.
	 *
	 * @param string      $name       The asset name in the `hyphen-separated-format`
	 * @param array       $deps       An array of dependency handles
	 * @param string      $vendor_url URL to vendor scripts and styles dir
	 * @param string      $prefix     MT script and style prefix
	 * @param Tribe__Main $tec        An instance of the main plugin class
	 */
	protected static function handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $tec ) {

		$asset = self::get_asset_factory_instance( $name );

		self::prepare_asset_package_request( $asset, $name, $deps, $vendor_url, $prefix, $tec );
	}

	/**
	 * Retrieves the appropriate asset factory instance
	 */
	protected static function get_asset_factory_instance( $name ) {
		$asset = Tribe__Events__Asset__Factory::instance()->make_for_name( $name );
		return $asset;
	}

	/**
	 * Filter the body class
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function body_class( $classes = array() ) {

		// view class
		$classes[] = $this->body_class;

		// category class
		if ( is_tax( Tribe__Events__Main::TAXONOMY ) ) {
			$classes[] = 'events-category';
			$category  = get_term_by( 'name', single_cat_title( '', false ), Tribe__Events__Main::TAXONOMY );
			$classes[] = 'events-category-' . $category->slug;
		}

		// archive class
		if ( ! is_single() || tribe_is_showing_all() ) {
			$single_id = array_search( 'single-tribe_events', $classes );
			if ( ! empty( $single_id ) ) {
				$classes[ $single_id ] = 'events-list';
			}
			$classes[] = 'events-archive';
		}

		// add selected style to body class for add-on styling
		$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

		switch ( $style_option ) {
			case 'skeleton':
				$classes[] = 'tribe-events-style-skeleton'; // Skeleton styles
				break;
			case 'full':
				$classes[] = 'tribe-events-style-full'; // Full styles
				break;
			default: // tribe styles is the default so add full and theme (tribe)
				$classes[] = 'tribe-events-style-full';
				$classes[] = 'tribe-events-style-theme';
				break;
		}

		return $classes;
	}

	/**
	 * Add classes to events on this view
	 *
	 * @return array
	 **/
	public function event_classes( $classes ) {
		return $classes;
	}

	/**
	 * Setup meta display in this template
	 *
	 * @deprecated 4.3
	 **/
	public function setup_meta() {
		_deprecated_function( __METHOD__, '4.3' );

		// customize meta items
		tribe_set_the_meta_template( 'tribe_event_venue_name', array(
				'before'       => '',
				'after'        => '',
				'label_before' => '',
				'label_after'  => '',
				'meta_before'  => '<span class="%s">',
				'meta_after'   => '</span>',
			) );
		tribe_set_meta_label( 'tribe_event_venue_address', '' );
		tribe_set_the_meta_template( 'tribe_event_venue_address', array(
				'before'       => '',
				'after'        => '',
				'label_before' => '',
				'label_after'  => '',
				'meta_before'  => '',
				'meta_after'   => '',
			) );
	}

	/**
	 * Set up the notices for this template
	 *
	 **/
	public function set_notices() {
		// By default we only display notices if no events could be found
		if ( have_posts() ) {
			return;
		}

		// Set an appropriate no-results-found message
		$this->nothing_found_notice();
	}

	/**
	 * Returns an array containing the search term, tax term and geographic term
	 * for the current request. Each may be empty.
	 *
	 * @return array
	 **/
	protected function get_search_terms() {
		global $wp_query;
		$tribe           = Tribe__Events__Main::instance();
		$geographic_term = '';
		$search_term     = '';
		$tax_term        = '';

		// Do we have a keyword or place name search?
		if ( ! empty( $wp_query->query_vars['s'] ) ) {
			$search_term = $wp_query->query_vars['s'];
		} elseif ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
			$search_term = $_REQUEST['tribe-bar-search'];
		} elseif ( ! empty( $_REQUEST['tribe-bar-geoloc'] ) ) {
			$geographic_term = $_REQUEST['tribe-bar-geoloc'];
		}
		if ( is_tax( $tribe->get_event_taxonomy() ) ) {
			$tax_term = get_term_by( 'slug', get_query_var( 'term' ), $tribe->get_event_taxonomy() );
			$tax_term = esc_html( $tax_term->name );
		}

		// Set an appropriate no-results-found message
		return array(
			$search_term,
			$tax_term,
			$geographic_term,
		);
	}

	/**
	 * Sets an appropriate no results found message. This may be overridden in child classes.
	 */
	protected function nothing_found_notice() {
		$events_label_plural = tribe_get_event_label_plural_lowercase();

		list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

		$tribe = Tribe__Events__Main::instance();

		if ( ! empty( $search_term ) ) {
			Tribe__Notices::set_notice( 'event-search-no-results', sprintf( esc_html__( 'There were no results found for %s.', 'the-events-calendar' ), '<strong>"' . esc_html( $search_term ) . '"</strong>' ) );
		} elseif ( ! empty( $geographic_term ) ) {
			Tribe__Notices::set_notice( 'event-search-no-results', sprintf( esc_html__( 'No results were found for %1$s in or near %2$s.', 'the-events-calendar' ), $events_label_plural, '<strong>"' . esc_html( $geographic_term ) . '"</strong>' ) );
		} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() && ( date( 'Y-m-d' ) === date( 'Y-m-d', strtotime( $tribe->date ) ) ) ) {
			Tribe__Notices::set_notice( 'events-not-found', sprintf( esc_html__( 'No upcoming %1$s listed under %2$s. Check out upcoming %3$s for this category or view the full calendar.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
		} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() ) {
			Tribe__Notices::set_notice( 'events-not-found', sprintf( esc_html__( 'No matching %1$s listed under %2$s. Please try viewing the full calendar for a complete list of %3$s.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
		} elseif ( ! empty( $tax_term ) && tribe_is_past() ) {
			Tribe__Notices::set_notice( 'events-past-not-found', sprintf( esc_html__( 'No previous %s ', 'the-events-calendar' ), $events_label_plural ) );
		} // if on any other view and attempting to view a category archive.
		elseif ( ! empty( $tax_term ) ) {
			Tribe__Notices::set_notice( 'events-not-found', sprintf( esc_html__( 'No matching %1$s listed under %2$s. Please try viewing the full calendar for a complete list of %3$s.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
		} else {
			Tribe__Notices::set_notice( 'event-search-no-results', esc_html__( 'There were no results found.', 'the-events-calendar' ) );
		}
	}

	/**
	 * Setup the view, query hijacking, etc. This happens right before the view file is included
	 *
	 **/
	public function setup_view() {

		global $wp_query;

		// don't show past posts in reverse order
		if ( $wp_query->tribe_is_past ) {
			$wp_query->posts = array_reverse( $wp_query->posts );
		}

		// set up the excerpt
		if ( is_int( $this->excerpt_length ) ) {
			add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
		}
		if ( is_string( $this->excerpt_more ) ) {
			add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		}
	}

	/**
	 * Apply filter to the title tag
	 *
	 * @param string|array $title
	 * @param string|null $sep
	 *
	 * @return mixed|void
	 */
	final public function title_tag( $title, $sep = null ) {
		// WP >= 4.4 has deprecated wp_title. This conditional (and the lower one) adds support for
		// the new and improved wp_get_document_title method and subsequent document_title_parts filter
		if ( 'document_title_parts' === current_filter() ) {
			$sep = apply_filters( 'document_title_separator', '-' );
			$the_title = $title['title'];
		} else {
			$the_title = $title;
		}

		$new_title = $this->get_title( $the_title, $sep );
		$the_title = apply_filters( 'tribe_events_title_tag', $new_title, $the_title, $sep );

		if ( 'document_title_parts' === current_filter() ) {
			$title['title'] = $the_title;
			return $title;
		}

		return $the_title;
	}

	/**
	 * Get the title for the view
	 *
	 * @param      $title
	 * @param null $sep
	 *
	 * @return string
	 */
	protected function get_title( $title, $sep = null ) {
		return tribe_get_events_title( false ) . ' ' . $sep . ' ' . $title;
	}

	/**
	 * Echo open tags for wrapper around view
	 *
	 **/
	public function view_wrapper_open() {
		echo '<div id="tribe-events-content-wrapper" class="tribe-clearfix">';
	}

	/**
	 * Output an input to store the hash for the current query
	 *
	 **/
	public function add_input_hash() {
		echo '<input type="hidden" id="tribe-events-list-hash" value="">';
	}

	/**
	 * Echo open tags for wrapper around view
	 *
	 **/
	public function view_wrapper_close() {
		echo '</div> <!-- #tribe-events-content-wrapper -->';
	}

	/**
	 * Function to execute when ajax view is requested
	 */
	public function ajax_response() {
		die();
	}

	/**
	 * Shutdown the view, restore the query, etc. This happens right after the view file is included
	 *
	 **/
	public function shutdown_view() {
		$this->unhook();
	}

	/**
	 * Unhook all the hooks set up on this view
	 *
	 **/
	protected function unhook() {

		// reset the excerpt
		if ( is_int( $this->excerpt_length ) ) {
			remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
		}
		if ( is_string( $this->excerpt_more ) ) {
			remove_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		}

		// set up queries, vars, etc that needs to be used in this view
		remove_action( 'tribe_events_before_view', array( $this, 'setup_view' ) );

		// set notices
		remove_action( 'tribe_events_before_view', array( $this, 'set_notices' ) );

		// Remove the comments template
		if ( ! ( tribe_get_option( 'tribeEventsTemplate', 'default' ) == '' ) ) {
			remove_filter( 'comments_template', array( $this, 'remove_comments_template' ) );
		}

		// set up meta used in this view
		remove_action( 'tribe_events_before_view', array( $this, 'setup_meta' ) );

		// cleanup after view (reset query, etc)
		remove_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

		// add wrapper html and input hash to non-ajax request
		remove_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
		remove_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
		remove_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

		// hide sensitive event info if post is password protected
		remove_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

		// add body class
		remove_filter( 'body_class', array( $this, 'body_class' ) );

		// event classes
		remove_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

	}

	/**
	 * Add/remove filters to hide/show sensitive event info on password protected posts
	 *
	 * @param WP_Post $post
	 *
	 **/
	public function manage_sensitive_info( $post ) {
		if ( post_password_required( $post ) ) {
			add_filter( 'tribe_events_event_schedule_details', '__return_null' );
			add_filter( 'tribe_events_recurrence_tooltip', '__return_null' );
			add_filter( 'tribe_event_meta_venue_name', '__return_null' );
			add_filter( 'tribe_event_meta_venue_address', '__return_null' );
			add_filter( 'tribe_event_featured_image', '__return_null' );
			add_filter( 'tribe_get_venue', '__return_null' );
		} else {
			remove_filter( 'tribe_events_event_schedule_details', '__return_null' );
			remove_filter( 'tribe_events_recurrence_tooltip', '__return_null' );
			remove_filter( 'tribe_event_meta_venue_name', '__return_null' );
			remove_filter( 'tribe_event_meta_venue_address', '__return_null' );
			remove_filter( 'tribe_event_featured_image', '__return_null' );
			remove_filter( 'tribe_get_venue', '__return_null' );
		}
	}

	/**
	 * Return an empty file as the comments template (to disable comments)
	 *
	 * @param string $template
	 *
	 * @return string
	 **/
	public function remove_comments_template( $template ) {
		return Tribe__Events__Main::instance()->pluginPath . 'src/admin-views/no-comments.php';
	}

	/**
	 * Limit the excerpt length on this template
	 *
	 * @param $length
	 *
	 * @return int
	 */
	public function excerpt_length( $length ) {
		return $this->excerpt_length;
	}

	/**
	 * Set up the excerpt more text on this template
	 *
	 * @param int $more
	 *
	 * @return int
	 */
	public function excerpt_more( $more ) {
		return $this->excerpt_more;
	}

	/**
	 * Check if comments are disabled on this view
	 *
	 * @param $option_value
	 * @param $option_name
	 *
	 * @return int
	 * @internal param int $more
	 *
	 */
	public function comments_off( $option_value, $option_name ) {
		if ( $option_name != 'showComments' ) {
			return $option_value;
		}

		if ( $this->comments_off == true ) {
			return false;
		}

		return $option_value;

	}
}
