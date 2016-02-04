<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * When using Select2 with AJAX we need a Hook to answer the JS in the proper format
 *
 * @since 4.1
 */
class Tribe__Events__Ajax__Select2 {

	/**
	 * We use a Private constructor to Prevent `new Class` usage
	 * Correct usage: `Tribe__Events__Ajax__Select2::instance()`
	 *
	 * @since  4.1
	 * @return  void
	 */
	private function __construct() {

	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @since  4.1
	 * @return Tribe__Events__Ajax__Select2
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Add the necessary hooks as the correct moment in WordPress
	 *
	 * @since  4.1
	 * @return  void
	 */
	public static function hook() {
		$myself = self::instance();

		// Setup Public and Private to Search Venues
		add_action( 'wp_ajax_tribe_select2_search_venues', array( $myself, 'search_venues' ) );
		add_action( 'wp_ajax_nopriv_tribe_select2_search_venues', array( $myself, 'search_venues' ) );

		// Setup Public and Private to Search Organizers
		add_action( 'wp_ajax_tribe_select2_search_organizers', array( $myself, 'search_organizers' ) );
		add_action( 'wp_ajax_nopriv_tribe_select2_search_organizers', array( $myself, 'search_organizers' ) );
	}

	/**
	 * Response to Select2 to search Venues
	 * This will produce a HTTP response with a JSON in the Select2 format
	 *
	 * @return HTTP wp_send_json
	 */
	public function search_venues() {
		$response = (object) array(
			'items' => array(),
		);

		$tec = Tribe__Events__Main::instance();
		$post_type_object = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE );
		$page = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 0;

		$query_args = array(
			'post_type' => Tribe__Events__Main::VENUE_POST_TYPE,
			'posts_per_page' => 20,
			'post_status' => array( 'publish', 'draft', 'private', 'pending' ),
			'ignore_sticky_posts ' => 1,
			'orderby' => 'title',
			'order' => 'ASC',
			'paged' => $page,

			's' => $_GET['q'],

			// Speed up stuff
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		// Actually do the Search
		$search = new WP_Query( $query_args );

		// Setup if there are more pages to query
		$response->more = $search->max_num_pages > $page;

		if ( $page < 2 && ! empty( $post_type_object->cap->create_posts ) && current_user_can( $post_type_object->cap->create_posts ) ) {
			$response->items[] = array(
				'id' => 0,
				'text' => esc_html( sprintf( __( 'Use New %s', 'the-events-calendar' ), $tec->singular_venue_label ) ),
			);
		}

		// If there are no events
		if ( ! $search->have_posts() ) {
			return wp_send_json( $response );
		}

		// Fetch User Data
		$current_user_id = get_current_user_id();
		$is_logged = is_user_logged_in();

		foreach ( $search->posts as $key => $venue ) {
			// Prepare Venue OPT
			$opt = array(
				'id' => $venue->ID,
				'text' => wp_kses( get_the_title( $venue->ID ), array() ),
				'address' => esc_attr( $tec->fullAddressString( $venue->ID ) ),
				'owner' => 'other',
			);

			// Check if logged and using == because `$venue->post_author` is a string not int
			if ( $is_logged && $venue->post_author == $current_user_id ) {
				$opt['owner'] = 'me';
			} else {
				// Skip non public venues if the user has no permissions
				if ( 'publish' !== $venue->post_status && ! current_user_can( 'edit_others_tribe_venues' ) ) {
					continue;
				}
			}

			$response->items[] = $opt;
		}

		return wp_send_json( $response );
	}

	/**
	 * Response to Select2 to search Organizers
	 * This will produce a HTTP response with a JSON in the Select2 format
	 *
	 * @return HTTP wp_send_json
	 */
	public function search_organizers() {
		$response = (object) array(
			'items' => array(),
		);

		$tec = Tribe__Events__Main::instance();
		$post_type_object = get_post_type_object( Tribe__Events__Main::ORGANIZER_POST_TYPE );
		$page = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 0;

		$query_args = array(
			'post_type' => Tribe__Events__Main::ORGANIZER_POST_TYPE,
			'posts_per_page' => 20,
			'post_status' => array( 'publish', 'draft', 'private', 'pending' ),
			'ignore_sticky_posts ' => 1,
			'orderby' => 'title',
			'order' => 'ASC',
			'paged' => $page,

			's' => $_GET['q'],

			// Speed up stuff
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		// Actually do the Search
		$search = new WP_Query( $query_args );

		// Setup if there are more pages to query
		$response->more = $search->max_num_pages > $page;

		if ( $page < 2 && ! empty( $post_type_object->cap->create_posts ) && current_user_can( $post_type_object->cap->create_posts ) ) {
			$response->items[] = array(
				'id' => 0,
				'text' => esc_html( sprintf( __( 'Use New %s', 'the-events-calendar' ), $tec->singular_organizer_label ) ),
			);
		}

		// If there are no events
		if ( ! $search->have_posts() ) {
			return wp_send_json( $response );
		}

		// Fetch User Data
		$current_user_id = get_current_user_id();
		$is_logged = is_user_logged_in();

		foreach ( $search->posts as $key => $organizer ) {
			// Prepare Venue OPT
			$opt = array(
				'id' => $organizer->ID,
				'text' => wp_kses( get_the_title( $organizer->ID ), array() ),
				'owner' => 'other',
			);

			// Check if logged and using == because `$organizer->post_author` is a string not int
			if ( $is_logged && $organizer->post_author == $current_user_id ) {
				$opt['owner'] = 'me';
			} else {
				// Skip non public venues if the user has no permissions
				if ( 'publish' !== $organizer->post_status && ! current_user_can( 'edit_others_tribe_organizers' ) ) {
					continue;
				}
			}

			$response->items[] = $opt;
		}

		return wp_send_json( $response );
	}
}