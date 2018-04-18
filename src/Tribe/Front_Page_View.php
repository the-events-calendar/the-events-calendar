<?php
/**
 * Provides an option to position the main events view on the site homepage.
 */
class Tribe__Events__Front_Page_View {


	/**
	 * The ID used to identify the virtual page, using a -10 for no particular reason, but avoding -1 as is regular
	 * used as infinite or any other popular reference.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	private $home_virtual_ID = -10;

	public function hook() {

		// Prevent breaking changing on sites with the old implementation
		add_action( 'admin_init', array( $this, 'backwards_compatible' ) );

		// Allow to set negative numbers on the homepage.
		add_action( 'sanitize_option_show_on_front', array( $this, 'save_show_on_front' ), 10, 3 );
		add_action( 'sanitize_option_page_on_front', array( $this, 'save_page_on_front' ), 10, 3 );
		// Insert the main Events page on the Customizer and Reading settings option
		add_filter( 'wp_dropdown_pages', array( $this, 'add_events_page_option' ), 10, 3 );

		if ( tribe_get_option( 'front_page_event_archive', false ) ) {
			// Implement front page view
			add_action( 'parse_query', array( $this, 'parse_query' ), 5 );
			add_filter( 'tribe_events_getLink', array( $this, 'main_event_page_links' ) );
		}

		add_action( 'parse_query', array( $this, 'parse_customizer_query' ) );
	}

	/**
	 * Inspect and possibly adapt the main query in order to force the main events page to the
	 * front of the house.
	 *
	 * @param WP_Query $query
	 */
	public function parse_query( WP_Query $query ) {

		if ( $this->is_virtual_page_id( $query->get( 'page_id' ) ) ) {
			$query->is_home = true;
		}

		// We're only interested in the main query (when it runs in relation to the site homepage),
		// we also need to make an exception for compatibility with Community Events (WP_Route)
		if (
			! $query->is_main_query()
			|| ! $query->is_home()
			|| $query->is_posts_page
			|| $query->get( 'WP_Route' )
		) {
			return;
		}

		// We don't need this to run again after this point
		remove_action( 'parse_query', array( $this, 'parse_query' ), 5 );

		// Let's set the relevant flags in order to cause the main events page to show
		$query->set( 'page_id', 0 );
		$query->set( 'post_type', Tribe__Events__Main::POSTTYPE );
		$query->set( 'eventDisplay', 'default' );
		$query->set( 'tribe_events_front_page', true );

		// Some extra tricks required to help avoid problems when the default view is list view
		$query->is_page = false;
		$query->is_singular = false;
	}

	/**
	 * Parse the query when the customizer sends request to preview specifc page to avoid 404 pages
	 * or the wrong page.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query
	 */
	public function parse_customizer_query( $query ) {

		$data = tribe_get_request_var( 'customized', array() );

		if ( empty( $data ) ) {
			return;
		}

		$data = json_decode( wp_unslash( $data ), true );

		$does_not_have_data = empty( $data['show_on_front'] ) && empty( $data['page_on_front'] );
		if ( ! $query->is_main_query() || $does_not_have_data ) {
			return;
		}

		// Fallback to the data that is missing with the current settings
		$data = wp_parse_args( $data, array(
			'show_on_front' => get_option( 'show_on_front' ),
			'page_on_front' => get_option( 'page_on_front' ),
		) );


		if ( 'posts' === $data['show_on_front'] ) {
			$query->query_vars = wp_parse_args( $query->query_vars, array(
				'is_post_type_archive' => false,
				'post_type' => '',
				'eventDisplay' => '',
				'page_id' => 0,
			) );
			unset( $query->query_vars['is_post_type_archive'] );
			unset( $query->query_vars['post_type'] );
			unset( $query->query_vars['eventDisplay'] );
			unset( $query->query_vars['page_id'] );

			if ( ! empty ( $query->query['page_id'] ) ) {
				unset( $query->query['page_id'] );
			}

			$query->is_singular = false;
			$query->is_page = false;
			$query->is_home = true;

		} elseif ( 'page' === $data['show_on_front'] && $this->is_virtual_page_id( $data['page_on_front'] ) ) {
			$query->is_404  = false;
			$query->is_home = true;

			$query->set( 'page_id', 0 );
			$query->set( 'post_type', Tribe__Events__Main::POSTTYPE );
			$query->set( 'eventDisplay', 'default' );
			$query->set( 'tribe_events_front_page', true );

			$query->is_page     = false;
			$query->is_singular = false;
		}
	}

	/**
	 * Where TEC generates a link to the nominal main events page replace it with a link to the
	 * front page instead.
	 *
	 * We'll only do this if pretty permalinks are in use.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function main_event_page_links( $url ) {
		// Capture the main events URL and break it into its consituent pieces for future comparison
		static $event_url;

		if ( ! isset( $event_url ) ) {
			$event_url = parse_url( $this->get_main_events_url() );
		}

		// Don't interfere if we're using ugly permalinks
		if ( '' === get_option( 'permalink_structure' ) ) {
			return $url;
		}

		// Break apart the requested URL
		$current = parse_url( $url );

		// If the URLs can't be inspected then bail
		if ( false === $event_url || false === $current ) {
			return $url;
		}

		// If this is not a request for the main events URL, bail
		if ( $event_url['path'] !== $current['path'] || $event_url['host'] !== $current['host'] ) {
			return $url;
		}

		// Reform the query
		$query = ! empty( $current['query'] ) ? '?' . $current['query'] : '';

		return home_url() . $query;
	}

	/**
	 * Supplies the nominal main events page URL (ie, the regular /events/ page that is used
	 * when front page event view is not enabled).
	 *
	 * @return string
	 */
	protected function get_main_events_url() {
		$events_slug = tribe_get_option( 'eventsSlug', 'events' );

		if ( false !== strpos( get_option( 'permalink_structure' ), 'index.php' ) ) {
			return trailingslashit( home_url() . '/index.php/' . sanitize_title( $events_slug ) );
		} else {
			return trailingslashit( home_url() . '/' . sanitize_title( $events_slug ) );
		}
	}

	/**
	 * Return the $original_value to avoid convert to a positive integer if the $original_value is the same as
	 * the ID of the virtual page.
	 *
	 * @since TBD
	 *
	 * @param $value
	 * @param $option
	 * @param $original_value
	 *
	 * @return mixed
	 */
	public function save_page_on_front( $value, $option, $original_value ) {

		$is_front_page_event_archive = $this->is_virtual_page_id( $original_value );

		tribe_update_option( 'front_page_event_archive', $is_front_page_event_archive );

		return $is_front_page_event_archive ? $original_value : $value;
	}

	/**
	 * Add "Main Events Page" option to the Customizer's "Homepage Settings" and the reading settings of the admin
	 *
	 * @since TBD
	 *
	 * @param string $output HTML output for drop down list of pages.
	 * @param array  $args   The parsed arguments array.
	 * @param array  $pages  List of WP_Post objects returned by `get_pages()`
	 *
	 * @return string
	 */
	public function add_events_page_option( $output, $args, $pages ) {

		// Ensures we only modify the Homepage dropdown.
		$valid_names = array( '_customize-dropdown-pages-page_on_front', 'page_on_front' );
		if ( ! isset( $args['name'] ) || ! in_array( $args['name'], $valid_names ) ) {
			return $output;
		}

		$label = sprintf(
			esc_html_x( 'Main %s Page', 'Customizer static front page setting', 'the-events-calendar' ),
			tribe_get_event_label_plural()
		);

		$selected = $this->is_page_on_front() ? 'selected' : '';
		$option = '<option class="level-0" value="' . $this->get_virtual_id() . '" ' . $selected . '>' . $label . '</option></select>';
		return str_replace( '</select>', $option, $output );
	}

	/**
	 * Reset the values for:
	 *
	 * - page_on_front
	 * - page_for_posts
	 * - front_page_event_archive
	 *
	 * if only the value for show_on_front is changed.
	 *
	 * @since TBD
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function save_show_on_front( $value ) {
		if ( 'posts' === $value ) {
			tribe_update_option( 'front_page_event_archive', false );
			update_option( 'page_on_front', 0 );
			update_option( 'page_for_posts', 0 );
		} elseif ( 'page' === $value && $this->is_virtual_page_on_front() ) {
			tribe_update_option( 'front_page_event_archive', true );
		}
		return $value;
	}

	/**
	 * Make sure to set the correct values if we need to update old versions using the previous logic.
	 *
	 * @since TBD
	 * @return boolean
	 */
	public function backwards_compatible() {

		$modified = false;
		if ( $this->is_page_on_front() ) {
			return $modified;
		}

		// If the archive option is false we don't need to update anything
		if ( $this->has_event_archive_option() && 'posts' === get_option( 'show_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			$modified = update_option( 'page_on_front', $this->get_virtual_id() );
		}
		return $modified;
	}

	/**
	 * Returns `true` if the 'front_page_event_archive' is `true` and the `page_on_front` is same as the virtual page ID
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_page_on_front() {
		return $this->has_event_archive_option() && $this->is_virtual_page_on_front();
	}

	/**
	 * Returns `true` if the `front_page_event_archive` is `true` otherwise `false`
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function has_event_archive_option() {
		return tribe_get_option( 'front_page_event_archive', false );
	}

	/**
	 * Compares the value of the setting `page_on_front` is same as the one used for the virtual page ID.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_virtual_page_on_front() {
		return $this->is_virtual_page_id( get_option( 'page_on_front' ) );
	}

	/**
	 * Compare a value with the value used on the virtual page ID and converts the var $compare to an integer
	 * to make sure the strict comparision is done correctly between two integers.
	 *
	 * @since TBD
	 *
	 * @param $compare
	 *
	 * @return bool
	 */
	public function is_virtual_page_id( $compare ) {
		return $this->get_virtual_id() === (int) $compare;
	}

	/**
	 * Return the ID of the virtual page.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_virtual_id() {
		return $this->home_virtual_ID;
	}
}