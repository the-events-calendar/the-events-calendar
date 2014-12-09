<?php
/**
 * @for     Map Template
 * This file contains hooks and functions required to set up the map view.
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe_Events_Pro_Map_Template' ) ) {
	class Tribe_Events_Pro_Map_Template extends Tribe_PRO_Template_Factory {

		protected $body_class = 'events-list';
		const AJAX_HOOK = 'tribe_geosearch';

		/**
		 * Set up hooks for map view
		 *
		 * @return void
		 **/
		protected function hooks() {
			parent::hooks();
			add_filter( 'tribe_events_header_attributes', array( $this, 'header_attributes' ) );
			add_action( 'tribe_events_list_before_the_event_title', array( $this, 'add_event_distance' ) );
		}

		/**
		 * Add header attributes for map view
		 *
		 * @return string
		 **/
		public function header_attributes( $attrs ) {
			$attrs['data-startofweek'] = get_option( 'start_of_week' );
			$attrs['data-view']    = 'map';
			$attrs['data-baseurl'] = tribe_get_mapview_link();

			return apply_filters( 'tribe_events_pro_header_attributes', $attrs );
		}

		/**
		 * AJAX handler for the Map view
		 */
		function ajax_response() {

			$tribe_paged = ! empty( $_POST['tribe_paged'] ) ? $_POST['tribe_paged'] : 1;

			TribeEventsQuery::init();

			$defaults = array(
				'post_type'      => TribeEvents::POSTTYPE,
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 ),
				'paged'          => $tribe_paged,
				'post_status'    => array( 'publish' ),
				'eventDisplay'   => 'map',
			);

			$view_state = 'map';

			/* if past view */
			if ( ! empty( $_POST['tribe_event_display'] ) && $_POST['tribe_event_display'] == 'past' ) {
				$view_state = 'past';
				$defaults['eventDisplay'] = 'past';
			}

			if ( isset( $_POST['tribe_event_category'] ) ) {
				$defaults[ TribeEvents::TAXONOMY ] = $_POST['tribe_event_category'];
			}
			$query       = TribeEventsQuery::getEvents( $defaults, true );
			$have_events = ( 0 < $query->found_posts );

			if ( $have_events && TribeEventsGeoLoc::instance()->is_geoloc_query() ) {
				$lat = isset( $_POST['tribe-bar-geoloc-lat'] ) ? $_POST['tribe-bar-geoloc-lat'] : 0;
				$lng = isset( $_POST['tribe-bar-geoloc-lng'] ) ? $_POST['tribe-bar-geoloc-lng'] : 0;

				TribeEventsGeoLoc::instance()->assign_distance_to_posts( $query->posts, $lat, $lng );
			} elseif ( ! $have_events && isset( $_POST['tribe-bar-geoloc'] ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong>.', 'tribe-events-calendar-pro' ), esc_html( $_POST['tribe-bar-geoloc'] ) ) );
			} elseif ( ! $have_events && isset( $_POST['tribe_event_category'] ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), esc_html( $_POST['tribe_event_category'] ) ) );
			} elseif ( ! $have_events ) {
				TribeEvents::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar-pro' ) );
			}

			$response = array(
				'html'        => '',
				'markers'     => array(),
				'success'     => true,
				'tribe_paged' => $tribe_paged,
				'max_pages'   => $query->max_num_pages,
				'total_count' => $query->found_posts,
				'view'        => $view_state,
			);

			// @TODO: clean this up / refactor the following conditional
			if ( $have_events ) {
				global $wp_query, $post;
				$data                               = $query->posts;
				$post                               = $query->posts[0];
				$wp_query                           = $query;
				TribeEvents::instance()->displaying = 'map';

				ob_start();

				tribe_get_view( 'pro/map/content' );
				$response['html'] .= ob_get_clean();
				$response['markers'] = TribeEventsGeoLoc::instance()->generate_markers( $data );
			} else {
				global $wp_query;
				$wp_query = $query;
				TribeEvents::instance()->setDisplay();

				ob_start();

				tribe_get_view( 'pro/map/content' );
				$response['html'] .= ob_get_clean();
			}

			$response = apply_filters( 'tribe_events_ajax_response', $response );

			header( 'Content-type: application/json' );
			echo json_encode( $response );

			exit;

		}

	}
}
