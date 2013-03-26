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

	public function __construct() {
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_script' ) );

		add_action( 'tribe-events-bar-show-filters', array( $this, 'print_filters_helper' ) );
		add_action( 'tribe-events-bar-show-views', 	 array( $this, 'print_views_helper' ) );
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
		$tec = TribeEvents::instance();
		$active_views = apply_filters( 'tribe-events-bar-views', array() );
		$show_bar_filter = apply_filters( 'tribe_events_bar_should_show_filter', in_array( get_post_type(), array( TribeEvents::VENUE_POST_TYPE, TribeEvents::ORGANIZER_POST_TYPE ) ) ? false : true );
		$view_slugs = array();
		
		foreach ( $active_views as $view ) {
			$view_slugs[] = $view['displaying'];
			if( $show_bar_filter && $tec->displaying === $view['displaying'] ) {
				// we look at each view params and try to add the hook if supplied if not dump in on the tribe_events
				$event_bar_hook = !empty($view['event_bar_hook']) ? $view['event_bar_hook'] : 'tribe_events_before_html';
				add_filter( $event_bar_hook , array( __CLASS__, 'show' ), 30 );
			}
		}

		$is_tribe_view = ( ! empty( $wp_query->tribe_is_event_query ) && in_array( TribeEvents::instance()->displaying, $view_slugs ) );
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
		include $tec->pluginPath . "views/modules/bar.php";
		$html = ob_get_clean() . $content;

		return apply_filters( 'tribe_events_bar_show', $html, $filters, $views, $content );
	}

	/**
	 *	Load the CSSs and JSs only if the Bar will be shown
	 */
	public function load_script() {
		if ( $this->should_show() ) {

			Tribe_Template_Factory::asset_package( 'tribe-events-bar' );
			Tribe_Template_Factory::asset_package( 'select2' );
			Tribe_Template_Factory::asset_package( 'jquery-placeholder' );
			Tribe_Template_Factory::asset_package( 'bootstrap-datepicker' );

			do_action( 'tribe-events-bar-enqueue-scripts' );
		}
	}

	/**
	 * Helper function to echo the HTML filters in the Bar
	 *
	 * @static
	 *
	 * @param $filters
	 */
	public static function print_filters_helper( $filters ) {

		echo '<div id="tribe-bar-collapse-toggle">' . __( 'Find Events', 'tribe-events-calendar' ) . ' <span class="tribe-bar-toggle-arrow"></span></div>';
		echo 	'<div class="tribe-bar-filters">';
		foreach ( $filters as $filter ) {
			echo '<div class="' . esc_attr( $filter['name'] ) . '-filter">';
				echo '<label class="label-' . esc_attr( $filter['name'] ) . '" for="' . esc_attr( $filter['name'] ) . '">' . $filter['caption'] . '</label>';
				echo $filter['html'];
			echo '</div>';
		}

		echo '<div class="tribe-bar-submit">';
			echo '<input class="tribe-events-button tribe-no-param" type="submit" name="submit-bar" value="' . __( 'Find Events', 'tribe-events-calendar' ) . '"/>';
		echo '</div>';
		echo '</div>';
	}


	/**
	 *
	 * Helper function to echo the views dropdown in the Bar
	 *
	 * @static
	 *
	 * @param $views
	 */
	public static function print_views_helper( $views ) {

		$tec = TribeEvents::instance();

		$limit = apply_filters( 'tribe-events-bar-views-breakpoint', 0 );

		$open_wrap = '<div id="tribe-bar-views">';
		$open_inner_wrap = '<div class="tribe-bar-views-inner tribe-clearfix">';

		$open 		= '<label>View As</label>';

		echo $open_wrap;
		echo $open_inner_wrap;		

		if ( count( $views ) <= $limit ) {
			// Standard list navigation for larger screens
			$open    .= '<ul class="tribe-bar-view-list">';
			$close    = "</ul>";
			$current  = 'tribe-active';
			$open_el  = '<li><a class="tribe-bar-view tribe-events-button-grey tribe-icon-!VIEW! !CURRENT-ACTIVE!" href="!URL!">';
			$close_el = "</a></li>";
			// Select input for smaller screens
			$open_sel     = '<select class="tribe-bar-view-select tribe-select2 tribe-no-param" name="tribe-events-bar-view">';
			$close_sel    = "</select>";
			$current_sel  = 'selected';
			$open_sel_el  = '<option !CURRENT-ACTIVE! value="!URL!">';
			$close_sel_el = "</option>";

		} else {
			$open    .= '<select class="tribe-select2 tribe-no-param" name="tribe-bar-view">';
			$close    = "</select>";
			$current  = 'selected';
			$open_el  = '<option !CURRENT-ACTIVE! value="!URL!" data-view="!JSKEY!">';
			$close_el = "</option>";
		}

		$close_inner_wrap = '</div>'; // close .tribe-bar-views-inner
		$close_wrap = '</div>'; // close #tribe-bar-views

		// standard list navigation for larger screens or select depending on number of views
		echo '<h3 class="tribe-events-visuallyhidden">' . __( 'Event Views Navigation', 'tribe-events-calendar' ) . '</h3>';
		echo $open;

		foreach ( $views as $view ) {
			$item = str_replace( '!URL!', esc_url( $view['url'] ), $open_el );
			$item = str_replace( '!VIEW!', $view['displaying'], $item );
			$item = str_replace( '!JSKEY!', $view['displaying'], $item );

			if ( $tec->displaying === $view['displaying'] ) {
				$item = str_replace( '!CURRENT-ACTIVE!', $current, $item );
			} else {
				$item = str_replace( '!CURRENT-ACTIVE!', 'tribe-inactive', $item );
			}

			echo $item;

			echo $view['anchor'];
			echo $close_el;
		}

		echo $close;

		// at smaller sizes we use a media query to hide the view buttons
		// and move to a select input element, which is why we are using this
		// second foreach
		if ( count( $views ) <= $limit ) {
			echo $open_sel;

			foreach ( $views as $view ) {
				// select input for smaller screens
				$item = str_replace( '!URL!', esc_url( $view['url'] ), $open_sel_el );

				if ( $tec->displaying === $view['displaying'] ) {
					$item = str_replace( '!CURRENT!', $current_sel, $item );
				} else {
					$item = str_replace( '!CURRENT!', '', $item );
				}

				echo $item;

				echo $view['anchor'];
				echo $close_sel_el;
			}
			echo $close_sel;

		}
		
	// show user front-end settings only if ECP is active

		if ( class_exists( 'TribeEventsPro' ) ) {
			$hide_recurrence = isset( $_REQUEST['tribeHideRecurrence'] ) ? $_REQUEST['tribeHideRecurrence'] : tribe_get_option( 'hideSubsequentRecurrencesDefault', false );

			echo '<div class="tribe-bar-settings">';
			echo '<div class="tribe-bar-button-settings">' . __( '<span class="tribe-hide-text">User Settings</span>', 'tribe-events-calendar' ) . '</div>';

			echo '<div class="tribe-bar-drop-content">';
			echo '<h5>' . __( 'Event Settings', 'tribe-events-calendar' ) . '</h5>';
			echo '<label for="tribeHideRecurrence">';
			echo '<input type="checkbox" name="tribeHideRecurrence" value="1" ' . checked( $hide_recurrence, 1, false ) . '>' . __( 'Hide subsequent occurences of events in lists<br /><span>Check to hide all but the next iteration</span>', 'tribe-events-calendar' );
			echo '</label>';
			echo '<button type="button" name="settingsUpdate" class="tribe-events-button-grey">' . __( 'Update', 'tribe-events-calendar' ) . '</button>';
			echo '</div><!-- .tribe-bar-drop-content -->';
			echo '</div><!-- .tribe-bar-drop-content -->';
			
		}
		
			echo $close_inner_wrap;
			echo $close_wrap;
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
