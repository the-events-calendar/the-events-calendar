<?php
class TribeEventsBar {

	private static $instance;

	// Each row should be an assosiative array with three fields: name, caption and html (html is the markup of the field)
	private $filters = array();

	// Each row should be an assosiative array with three fields: displaying, anchor and url.
	// Displaying is the value of TribeEvents->displaying
	private $views = array();

	public function __construct() {
		add_filter( 'the_content', array( $this, 'show' ), 1 );
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_script' ) );
	}

	public function should_show() {
		global $wp_query;
		$is_tribe_view = !empty( $wp_query->tribe_is_event_query );
		return apply_filters( 'tribe-events-bar-should-show', $is_tribe_view );
	}

	/*
	 * Our Events views override the value of $content, so this filter needs to
	 * echo the bar instead of prepending it to the $content.
	 */

	public function show( $content ) {

		if ( $this->should_show() ) {

			add_filter( 'tribe-events-bar-should-show', '__return_false', 9999 );

			$filters = apply_filters( 'tribe-events-bar-filters', $this->filters );
			$views   = apply_filters( 'tribe-events-bar-views', $this->views );

			$tec = TribeEvents::instance();

			include $tec->pluginPath . "views/modules/bar.php";

		}

		return $content;
	}

	public function load_script() {
		if ( $this->should_show() ) {

			Tribe_Template_Factory::asset_package( 'tribe-events-bar' );
			Tribe_Template_Factory::asset_package( 'chosen' );
			Tribe_Template_Factory::asset_package( 'jquery-placeholder' );
			Tribe_Template_Factory::asset_package( 'datepicker' );

			do_action( 'tribe-events-bar-enqueue-scripts' );
		}
	}

	public static function print_filters_helper( $filters ) {

		echo '<form id="tribe-events-bar-form" name="tribe-events-bar-form" method="post" action="' . add_query_arg( array() ) . '">';

		echo '<div class="tribe-events-bar-toggle"><span class="tribe-triangle"></span><span class="tribe-events-visuallyhidden">More Filters</span></div>';

		foreach ( $filters as $filter ) {
			echo '<div class="tribe-events-bar-filter-wrap ' . esc_attr( $filter['name'] ) . '">';
			echo '<label class="tribe-events-visuallyhidden" for="' . esc_attr( $filter['name'] ) . '">' . $filter['caption'] . '</label>';
			echo $filter['html'];
			echo '</div>';
		}

		echo '<div class="tribe-events-bar-filter-wrap tribe-bar-submit"><input class="tribe-events-button-grey" type="submit" name="submit-bar" value="' . __( 'Search', 'tribe-events-calendar' ) . '"/></div>';

		echo '</form><!-- #tribe-events-bar-form -->';

	}


	public static function print_views_helper( $views ) {

		$tec = TribeEvents::instance();

		$limit = apply_filters( 'tribe-events-bar-views-breakpoint', 3 );

		if ( count( $views ) <= $limit ) {
			// Standard list navigation for larger screens
			$open     = '<ul class="tribe-events-bar-view-list">';
			$close    = "</ul>";
			$current  = 'tribe-active';
			$open_el  = '<li><a class="tribe-events-bar-view tribe-events-button-grey !CURRENT!" href="!URL!">';
			$close_el = "</a></li>";
			// Select input for smaller screens
			$open_sel     = '<select class="tribe-events-bar-view-select chzn-select" name="tribe-events-bar-view">';
			$close_sel    = "</select>";
			$current_sel  = 'selected';
			$open_sel_el  = '<option !CURRENT! value="!URL!">';
			$close_sel_el = "</option>";

		} else {

			$open     = '<select class="chzn-select" name="tribe-events-bar-view">';
			$close    = "</select>";
			$current  = 'selected';
			$open_el  = '<option !CURRENT! value="!URL!">';
			$close_el = "</option>";
		}

		// standard list navigation for larger screens or select depending on number of views
		echo '<h3 class="tribe-events-visuallyhidden">' . __( 'Event Views Navigation', 'tribe-events-calendar' ) . '</h3>';
		echo $open;

		foreach ( $views as $view ) {
			$item = str_replace( '!URL!', esc_url( $view['url'] ), $open_el );

			if ( $tec->displaying === $view['displaying'] ) {
				$item = str_replace( '!CURRENT!', $current, $item );
			} else {
				$item = str_replace( '!CURRENT!', 'tribe-inactive', $item );
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

	}

	/**
	 * @static
	 * @return TribeEventsBar
	 */
	public static function instance() {
		if ( !isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

}

TribeEventsBar::instance();