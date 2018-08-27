<?php
/**
 * @for     Events List Template
 * This file contains the hook logic required to create an effective event list view.
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Template__List' ) ) {
	/**
	 * List view template class
	 */
	class Tribe__Events__Template__List extends Tribe__Events__Template_Factory {

		protected $body_class = 'events-list';
		protected $asset_packages = array();

		const AJAX_HOOK = 'tribe_list';

		/**
		 * The path to the template file used for the view.
		 * This value is used in Shortcodes/Tribe_Events.php to
		 * locate the correct template file for each shortcode
		 * view.
		 *
		 * @var string
		 */
		public $view_path = 'list/content';

		protected function hooks() {
			parent::hooks();

			tribe_asset_enqueue( 'tribe-events-list' );

			if ( tribe_is_showing_all() ) {
				add_filter( 'tribe_get_template_part_path_modules/bar.php', '__return_false' );
			}
		}

		/**
		 * Get the title for list view
		 * @param      $title
		 * @param null $sep
		 *
		 * @return string
		 */
		protected function get_title( $original_title, $sep = null ) {
			$new_title = parent::get_title( $original_title, $sep );
			if ( tribe_is_upcoming() && has_filter( 'tribe_upcoming_events_title' ) ) {
				_deprecated_function( "The 'tribe_upcoming_events_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$new_title = apply_filters( 'tribe_upcoming_events_title', $new_title, $sep );
			} elseif ( has_filter( 'tribe_past_events_title' ) ) {
				_deprecated_function( "The 'tribe_past_events_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$new_title = apply_filters( 'tribe_past_events_title', $new_title, $sep );
			}
			return $new_title;
		}

		/**
		 * List view ajax handler
		 *
		 */
		public function ajax_response() {

			Tribe__Events__Query::init();

			$tribe_paged = ( ! empty( $_POST['tribe_paged'] ) ) ? intval( $_POST['tribe_paged'] ) : 1;
			$post_status = array( 'publish' );
			if ( is_user_logged_in() ) {
				$post_status[] = 'private';
			}

			$args = array(
				'eventDisplay' => 'list',
				'post_type'    => Tribe__Events__Main::POSTTYPE,
				'post_status'  => $post_status,
				'paged'        => $tribe_paged,
				'featured'     => tribe( 'tec.featured_events' )->featured_events_requested(),
			);

			// check & set display
			if ( isset( $_POST['tribe_event_display'] ) ) {
				if ( 'past' === $_POST['tribe_event_display'] ) {
					$args['eventDisplay'] = 'past';
					$args['order'] = 'DESC';
				} elseif ( 'all' === $_POST['tribe_event_display'] ) {
					$args['eventDisplay'] = 'all';
				}
			}

			// check & set event category
			if ( isset( $_POST['tribe_event_category'] ) ) {
				$args[ Tribe__Events__Main::TAXONOMY ] = $_POST['tribe_event_category'];
			}

			$args = apply_filters( 'tribe_events_listview_ajax_get_event_args', $args, $_POST );

			$query = tribe_get_events( $args, true );

			// $hash is used to detect whether the primary arguments in the query have changed (i.e. due to a filter bar request)
			// if they have, we want to go back to page 1
			$hash = $query->query_vars;

			$hash['paged']      = null;
			$hash['start_date'] = null;
			$hash['end_date']   = null;
			$hash['search_orderby_title'] = null;
			$hash_str           = md5( maybe_serialize( $hash ) );

			if ( ! empty( $_POST['hash'] ) && $hash_str !== $_POST['hash'] ) {
				$tribe_paged   = 1;
				$args['paged'] = 1;
				$query         = tribe_get_events( $args, true );
			}


			$response = array(
				'html'        => '',
				'success'     => true,
				'max_pages'   => $query->max_num_pages,
				'hash'        => $hash_str,
				'tribe_paged' => $tribe_paged,
				'total_count' => $query->found_posts,
				'view'        => 'list',
			);

			global $post;
			global $paged;
			global $wp_query;

			$wp_query = $query;

			if ( ! empty( $query->posts ) ) {
				$post = $query->posts[0];
			}

			$paged = $tribe_paged;

			Tribe__Events__Main::instance()->displaying = apply_filters( 'tribe_events_listview_ajax_event_display', 'list', $args );

			if ( ! empty( $_POST['tribe_event_display'] ) && 'past' === $_POST['tribe_event_display'] ) {
				$response['view'] = 'past';
			}

			ob_start();
			tribe_get_view( 'list/content' );
			$response['html'] .= ob_get_clean();

			apply_filters( 'tribe_events_ajax_response', $response );

			header( 'Content-type: application/json' );
			echo json_encode( $response );

			die();
		}
	}
}
