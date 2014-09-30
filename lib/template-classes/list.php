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

if ( ! class_exists( 'Tribe_Events_List_Template' ) ) {
	/**
	 * List view template class
	 */
	class Tribe_Events_List_Template extends Tribe_Template_Factory {

		protected $body_class = 'events-list';
		protected $asset_packages = array( 'ajax-list' );

		const AJAX_HOOK = 'tribe_list';

		protected function hooks() {
			parent::hooks();
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
		 * @return void
		 */
		function ajax_response() {

			TribeEventsQuery::init();

			$tribe_paged = ( ! empty( $_POST['tribe_paged'] ) ) ? intval( $_POST['tribe_paged'] ) : 1;

			$args = array(
				'eventDisplay' => 'list',
				'post_type'    => TribeEvents::POSTTYPE,
				'post_status'  => 'publish',
				'paged'        => $tribe_paged
			);

			// check & set past display
			if ( isset( $_POST['tribe_event_display'] ) && $_POST['tribe_event_display'] == 'past' ) {
				$args['eventDisplay'] = 'past';
			}

			// check & set event category
			if ( isset( $_POST['tribe_event_category'] ) ) {
				$args[TribeEvents::TAXONOMY] = $_POST['tribe_event_category'];
			}

			$query = TribeEventsQuery::getEvents( $args, true );

			// $hash is used to detect whether the primary arguments in the query have changed (i.e. due to a filter bar request)
			// if they have, we want to go back to page 1
			$hash = $query->query_vars;

			$hash['paged']      = null;
			$hash['start_date'] = null;
			$hash['end_date']   = null;
			$hash_str           = md5( maybe_serialize( $hash ) );

			if ( ! empty( $_POST['hash'] ) && $hash_str !== $_POST['hash'] ) {
				$tribe_paged   = 1;
				$args['paged'] = 1;
				$query         = TribeEventsQuery::getEvents( $args, true );
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

			global $wp_query, $post, $paged;
			$wp_query = $query;
			if ( ! empty( $query->posts ) ) {
				$post = $query->posts[0];
			}

			$paged = $tribe_paged;

			TribeEvents::instance()->displaying = 'list';

			if ( ! empty( $_POST['tribe_event_display'] ) && $_POST['tribe_event_display'] == 'past' ){
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
