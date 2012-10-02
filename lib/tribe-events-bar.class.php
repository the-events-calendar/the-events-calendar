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

			$this->load_script();

			$filters = apply_filters( 'tribe-events-bar-filters', $this->filters );
			$views   = apply_filters( 'tribe-events-bar-views', $this->views );

			$tec = TribeEvents::instance();

			include $tec->pluginPath . "views/modules/bar.php";

		}

		return $content;
	}

	private function load_script() {
		$tec = TribeEvents::instance();
		wp_enqueue_script( 'tribe-events-bar', $tec->pluginUrl . 'resources/tribe-events-bar.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	public static function print_filters_helper( $filters ) {

		echo '<form id="tribe-events-bar-form" name="tribe-events-bar-form" method="post" action="'. add_query_arg( array() ) .'">';

		foreach ( $filters as $filter ) {
			echo '<div class="tribe-events-bar-filter-wrap">';
			echo '<label class="tribe-events-visuallyhidden" for="' . esc_attr( $filter['name'] ) . '">' . $filter['caption'] . '</label>';
			echo $filter['html']; 
			echo '</div>';
		}

		echo '<div class="tribe-events-bar-filter-wrap"><input type="submit" name="submit-bar" value="'. __( 'Search', 'tribe-events-calendar' ) .'"/></div>';

		echo '</form><!-- #tribe-events-bar-form -->';

	}


	public static function print_views_helper( $views ) {

		$tec = TribeEvents::instance();

		$limit = apply_filters( 'tribe-events-bar-views-breakpoint', 3 );

		if ( count( $views ) <= $limit ) {
			$open     = "<ul>";
			$close    = "</ul>";
			$current  = 'active';
			$open_el  = "<li><a class='tribe-events-bar-view !CURRENT!' href='!URL!'>";
			$close_el = "</a></li>";

		} else {

			$open     = "<select name='tribe-events-bar-view'>";
			$close    = "</select>";
			$current  = 'selected';
			$open_el  = "<option !CURRENT! value='!URL!'>";
			$close_el = "</option>";
		}

		echo $open;

		foreach ( $views as $view ) {
			$item = str_replace( '!URL!', esc_url( $view['url'] ), $open_el );

			if ( $tec->displaying === $view['displaying'] ) {
				$item = str_replace( '!CURRENT!', $current, $item );
			} else {
				$item = str_replace( '!CURRENT!', '', $item );
			}

			echo $item;

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