<?php
class TribeEventsBar {

	private static $instance;

	// Each row should be a string with the HTML markup for the filter
	private $filters = array();

	// Each row should be an assosiative array with two fields: anchor and url.
	private $views = array();

	public function __construct() {
		add_filter( 'the_content', array( $this, 'show' ), 1 );
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

			$filters = apply_filters( 'tribe-events-bar-filters', $this->filters );
			$views   = apply_filters( 'tribe-events-bar-views', $this->views );

			$tec = TribeEvents::instance();

			include $tec->pluginPath . "views/modules/bar.php";

		}

		return $content;
	}

	public static function print_views_helper( $views ) {

		$limit = apply_filters( 'tribe-events-bar-views-breakpoint', 5 );

		if ( count( $views ) <= $limit ) {
			$open  = "<ul>";
			$close = "</ul>";

			$open_el  = "<li><a href='!URL!'>";
			$close_el = "</a></li>";

		} else {

			$open  = "<select name='tribe-events-bar-view'>";
			$close = "</select>";

			$open_el  = "<option value='!URL!'>";
			$close_el = "</option>";
		}

		echo $open;

		foreach ( $views as $view ) {
			echo str_replace( '!URL!', esc_url( $view['url'] ), $open_el );
			echo $view['anchor'];
			echo $close_el;
		}

		echo $close;
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