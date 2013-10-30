<?php
/**
 *
 */
class TribeEventsBar {

	private static $instance;

	// Each row should be an assosiative array with three fields: name, caption and html (html is the markup of the field)
	private $filters = array();

	// Each row should be an assosiative array with three fields: displaying, anchor and url.
	// Displaying is the value of TribeEvents->displaying
	private $views = array();

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct() {
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_script' ), 9 );
		add_filter( 'body_class', array( $this, 'body_class') );
		add_action('tribe_events_bar_before_template',  array( $this, 'disabled_bar_before') );
		add_action('tribe_events_bar_after_template',  array( $this, 'disabled_bar_after') );

		// add_action( 'tribe-events-bar-show-filters', array( $this, 'print_filters_helper' ) );
		// add_action( 'tribe-events-bar-show-views', 	 array( $this, 'print_views_helper' ) );

	}

	/**
	 * Decide if the TribeBar should be shown in a particular pageview.
	 *
	 * @filter tribe-events-bar-views to get all the registred views that the Bar will show
	 * @filter tribe-events-bar-should-show to allow themers to always hide the bar if they want.
	 *
	 * To always hide the Bar, add this to your theme's functions.php:
	 * 		add_filter( 'tribe-events-bar-should-show', '__return_false' );
	 *
	 * @return bool
	 *
	 */
	public function should_show() {
		global $wp_query;
		$show_bar_filter = apply_filters( 'tribe-events-bar-should-show', in_array( get_post_type(), array( TribeEvents::VENUE_POST_TYPE, TribeEvents::ORGANIZER_POST_TYPE ) ) ? false : true );
		$is_tribe_view = ( ! empty( $wp_query->tribe_is_event_query ) && ! is_single() && $show_bar_filter );
		return apply_filters( 'tribe-events-bar-should-show', $is_tribe_view );
	}

	/**
	 * Add the Tribe Bar to the tribe_events_before_html filter.
	 * @param $content
	 *
	 * @filter tribe-events-bar-should-show set it to false to prevent infinite nesting
	 * @filter tribe-events-bar-filters to get the list of registered filters
	 * @filter tribe-events-bar-views to get the list of registered views
	 *
	 * To add filters:
	 *
	 * add_filter( 'tribe-events-bar-filters',  'setup_my_field_in_bar', 1, 1 );
	 *
	 * public function setup_my_field_in_bar( $filters ) {
	 *   $filters[] = array( 'name'    => 'tribe-bar-my-field',
	 *                       'caption' => 'My Field',
	 *                       'html'    => '<input type="text" name="tribe-bar-my-field" id="tribe-bar-my-field">' );
	 *   return $filters;
	 * }
	 *
	 * To add views:
	 *
	 * add_filter( 'tribe-events-bar-views',  'my_setup_view_for_bar', 10);
	 *
	 * public function my_setup_view_for_bar( $views ) {
	 *     $tec = TribeEvents::instance();
	 *     $views[] = array('displaying' => 'myview', 'anchor' => 'My view', 'url' =>  $tec->getOption( 'eventsSlug', 'events' ) . '/my_view_slug'  );
	 *     return $views;
	 * }
	 *
	 * @return string
	 */
	public function show( $content ) {

		$tec = TribeEvents::instance();

		//set it to false to prevent infinite nesting
		add_filter( 'tribe-events-bar-should-show', '__return_false', 9999 );

		// Load the registered filters and views for the Bar. This values will be used in the template.
		$filters = apply_filters( 'tribe-events-bar-filters', self::instance()->filters );
		$views   = apply_filters( 'tribe-events-bar-views', self::instance()->views );

		//Load the template
		ob_start();
		include TribeEventsTemplates::getTemplateHierarchy( 'bar.php', array('subfolder'=>'modules') );
		$html = ob_get_clean() . $content;

		echo apply_filters( 'tribe_events_bar_show', $html, $filters, $views, $content );
	}


	/**
	 * Adds a body class of tribe-bar-is-disabled when the Tribe Bar is disabled.
	 * 
	 * @return array The new body class array
	 * @author Kyle Unzicker
	 * @since 3.0
	 */	
	public function body_class( $classes ){
		if ( tribe_get_option('tribeDisableTribeBar', false) == true ) {
			$classes[] = 'tribe-bar-is-disabled';
		}
		return $classes;
	}

	/**
	 * Returns the opening tag of the disabled bar wrapper
	 * 
	 * @return string
	 * @author Kyle Unzicker
	 * @since 3.0
	 */	
	public function disabled_bar_before( $before ) {
		if ( tribe_get_option('tribeDisableTribeBar', false) == true ) {
			$before = '<div class="tribe-bar-disabled">';
			echo $before;
		}
	}

	/**
	 * Returns the closing tag of the disabled bar wrapper
	 * 
	 * @return array The new body class array
	 * @author Kyle Unzicker
	 * @since 3.0
	 */
	public function disabled_bar_after( $after ) {
		if ( tribe_get_option('tribeDisableTribeBar', false ) == true ) {
			$after = '</div>';
			echo $after;
		}
	}

	/**
	 *	Load the CSSs and JSs only if the Bar will be shown
	 */
	public function load_script() {

		if ($this->should_show()) {
			Tribe_Template_Factory::asset_package( 'jquery-placeholder' );
			Tribe_Template_Factory::asset_package( 'bootstrap-datepicker' );
			Tribe_Template_Factory::asset_package( 'tribe-events-bar' );

			do_action( 'tribe-events-bar-enqueue-scripts' );
		}
	}

	/**
	 * @static
	 * @return TribeEventsBar
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

}

TribeEventsBar::instance();
