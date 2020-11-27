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

use Tribe__Date_Utils as Dates;

if ( ! class_exists( 'Tribe__Events__Template__List' ) ) {
	/**
	 * List view template class
	 */
	class Tribe__Events__Template__List extends Tribe__Events__Template_Factory {

		protected $body_class = 'events-list';
		protected $asset_packages = [];

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

			$tribe_paged = absint( tribe_get_request_var( 'tribe_paged', 1 ) );
			$post_status = [ 'publish' ];
			if ( is_user_logged_in() ) {
				$post_status[] = 'private';
			}

			$display = tribe( 'context' )->get( 'event_display' );

			$args = [
				'eventDisplay' => $display,
				'post_type'    => Tribe__Events__Main::POSTTYPE,
				'post_status'  => $post_status,
				'paged'        => $tribe_paged,
			];

			// If the request is false or not set we assume the request is for all events, not just featured ones.
			if (
				tribe( 'tec.featured_events' )->featured_events_requested()
				|| (
					isset( $this->args['featured'] )
					&& tribe_is_truthy( $this->args['featured'] )
				)
			) {
				$args['featured'] = true;
			} else {
				/**
				 * Unset due to how queries featured argument is expected to be non-existent.
				 *
				 * @see #127272
				 */
				if ( isset( $args['featured'] ) ) {
					unset( $args['featured'] );
				}
			}

			if ( (bool) tribe_get_request_var( 'tribeHideRecurrence' ) ) {
				$args['hide_subsequent_recurrences'] = true;
			}

			// Apply display and date.
			$date = tribe_get_request_var( 'tribe-bar-date', 'now' );

			if ( 'now' === $date ) {
				/*
				 * When defaulting to "now" let's round down to the lower half hour.
				 * This way we avoid invalidating the hash on requests following each other
				 * in reasonable (30') time.
				 */
				$date = Dates::build_date_object( 'now' );
				$minutes = $date->format( 'm' );
				$date->setTime(
					$date->format( 'H' ),
					$minutes - ( $minutes % 30 )
				);
				$date = $date->format( Dates::DBDATETIMEFORMAT );
			}

			$args['eventDisplay'] = $display;

			if ( 'list' === $display ) {
				$args['ends_after'] = $date;
				$args['order'] = 'ASC';
			} elseif ( 'past' === $display ) {
				$args['ends_before'] = $date;
				$args['order'] = 'DESC';
			} elseif ( 'all' === $display ) {
				$args['start_date'] = $date;
				$args['order'] = 'ASC';
			}

			// Check & set event category.
			if ( isset( $_POST['tribe_event_category'] ) ) {
				$args[ Tribe__Events__Main::TAXONOMY ] = $_POST['tribe_event_category'];
			}

			$args = apply_filters( 'tribe_events_listview_ajax_get_event_args', $args, $_POST );

			$query = tribe_get_events( $args, true );

			/*
			 * The hash is used to detect whether the primary arguments in the query have changed (i.e. due to a filter
			 * bar request); if they have, we want to go back to page 1.
			 */
			$hash_str = $query->builder->hash( [
				'exclude' => [
					'paged',
					'start_date',
					'ends_before',
					'ends_after',
				],
			], $query );

			if ( ! empty( $_POST['hash'] ) && $hash_str !== $_POST['hash'] ) {
				$tribe_paged   = 1;
				$args['paged'] = 1;
				$query         = tribe_get_events( $args, true );
			}

			$response = [
				'html'        => '',
				'success'     => true,
				'max_pages'   => $query->max_num_pages,
				'hash'        => $hash_str,
				'tribe_paged' => $tribe_paged,
				'total_count' => $query->found_posts,
				'view'        => 'list',
			];

			global $post;
			global $paged;
			global $wp_query;

			$wp_query = $query;

			if ( ! empty( $query->posts ) ) {
				$post = $query->posts[0];
			}

			$paged = absint( $tribe_paged );

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
