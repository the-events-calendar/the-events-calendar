<?php

/**
 * Holds methods that are required to maintain backwards compatibility with minor versions
 */
class Tribe__Events__Backcompat {

	private static $instance = null;

	public static function init() {
		self::instance()->add_hooks();
	}

	/**
	 * Set up any needed hooks for methods in this class
	 */
	public function add_hooks() {
		add_filter( 'tribe_get_single_option', array( $this, 'filter_multiday_cutoff' ), 10, 3 );
		add_filter( 'tribe_get_single_option', array( $this, 'filter_default_view' ), 10, 3 );
		add_filter( 'tribe_get_single_option', array( $this, 'filter_enabled_views' ), 10, 3 );
		add_action( 'parse_query', array( $this, 'change_qv_to_list' ), 45 );
	}


	/**
	 * We used to store midnight as 12:00. It should be 00:00.
	 *
	 * @param string $cutoff
	 * @param string $default
	 * @param string $option
	 *
	 * @return string
	 */
	public function filter_multiday_cutoff( $cutoff, $default, $option ) {
		if ( $option == 'multiDayCutoff' ) {
			$value = explode( ':', $cutoff );
			if ( $value[0] == '12' ) {
				$value[0] = '00';
				$cutoff   = implode( ':', $value );
			}
		}

		return $cutoff;
	}

	/**
	 * Change 'upcoming' to 'list' in the default view option (upcoming was removed in 3.8)
	 *
	 * @param string $default_view
	 * @param string $default
	 * @param string $option
	 *
	 * @return string
	 */
	public function filter_default_view( $default_view, $default, $option ) {
		if ( $option == 'viewOption' ) {
			if ( $default_view == 'upcoming' ) {
				$default_view = 'list';
			}
		}

		return $default_view;
	}

	/**
	 * Change 'upcoming' to 'list' in the enabled views option (upcoming was removed in 3.8)
	 *
	 * @param string $enabled_views
	 * @param string $default
	 * @param string $option
	 *
	 * @return array
	 */
	public function filter_enabled_views( $enabled_views, $default, $option ) {
		if ( $option == 'tribeEnableViews' ) {
			foreach ( $enabled_views as &$view ) {
				if ( $view == 'upcoming' ) {
					$view = 'list';
				}
			}
		}
		return $enabled_views;
	}

	/**
	 * Change legacy eventDisplay query var from past/upcoming to list (past & upcoming were removed in 3.8)
	 *
	 * @param $query
	 */
	public function change_qv_to_list( $query ) {

		if ( $query->get( 'eventDisplay' ) == 'upcoming' ) {
			_deprecated_argument( 'tribe_get_events', '3.8', "Setting eventDisplay to 'upcoming' is deprecated. Please use 'list' instead." );
			$query->set( 'eventDisplay', 'list' );
		}

		if ( $query->get( 'eventDisplay' ) == 'past' ) {
			$query->set( 'eventDisplay', 'list' );
			$query->tribe_is_past = true;
		}
	}

	/**
	 * @return self
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

} // Tribe__Events__Backcompat
