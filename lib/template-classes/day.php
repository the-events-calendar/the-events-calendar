<?php
/**
 * @for     Day Template
 * This file contains hooks and functions required to set up the day view.
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe_Events_Day_Template' ) ) {
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		protected $body_class = 'tribe-events-day';
		protected $asset_packages = array( 'ajax-dayview' );

		const AJAX_HOOK = 'tribe_event_day';

		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 **/
		public function hooks() {

			parent::hooks();

			add_filter( 'tribe_get_ical_link', array( $this, 'ical_link' ), 20, 1 );
			add_filter( 'tribe_events_header_attributes', array( $this, 'header_attributes' ) );
		}

		/**
		 * Add header attributes for day view
		 *
		 * @return string
		 **/
		public function header_attributes( $attrs ) {

			global $wp_query;
			$current_day = $wp_query->get( 'start_date' );

			$attrs['data-view']    = 'day';
			$attrs['data-baseurl'] = tribe_get_day_link( $current_day );
			$attrs['data-date']    = Date( 'Y-m-d', strtotime( $current_day ) );
			$attrs['data-header']  = Date( tribe_get_date_format( true ), strtotime( $current_day ) );

			return $attrs;
		}

		/**
		 * Get the title for day view
		 * @param      $title
		 * @param null $sep
		 *
		 * @return string
		 */
		protected function get_title( $original_title, $sep = null ) {
			$new_title = parent::get_title( $original_title, $sep );
			if ( has_filter( 'tribe_events_day_view_title' ) ) {
				_deprecated_function( "The 'tribe_events_day_view_title' filter", '3.8', " the 'tribe_get_events_title' filter" );
				$title_date = date_i18n( tribe_get_date_format( true ), strtotime( get_query_var( 'eventDate' ) ) );
				$new_title  = apply_filters( 'tribe_events_day_view_title', $new_title, $sep, $title_date );
			}
			return $new_title;
		}


		/**
		 * Get the link to download the ical version of day view
		 * @param $link
		 *
		 * @return string
		 */
		public function ical_link( $link ) {
			global $wp_query;
			$day = $wp_query->get( 'start_date' );

			return trailingslashit( esc_url( trailingslashit( tribe_get_day_link( $day ) ) . '?ical=1' ) );
		}

		/**
		 * Organize and reorder the events posts according to time slot
		 *
		 * @return void
		 **/
		public function setup_view() {

			global $wp_query;

			$time_format = apply_filters( 'tribe_events_day_timeslot_format', get_option( 'time_format', TribeDateUtils::TIMEFORMAT ) );

			if ( $wp_query->have_posts() ) {
				$unsorted_posts = $wp_query->posts;
				foreach ( $unsorted_posts as &$post ) {
					if ( tribe_event_is_all_day( $post->ID ) ) {
						$post->timeslot = __( 'All Day', 'tribe-events-calendar' );
					} else {
						if ( strtotime( tribe_get_start_date( $post->ID, true, TribeDateUtils::DBDATETIMEFORMAT ) ) < strtotime( $wp_query->get( 'start_date' ) ) ) {
							$post->timeslot = __( 'Ongoing', 'tribe-events-calendar' );
						} else {
							$post->timeslot = tribe_get_start_date( $post, false, $time_format );
						}
					}
				}
				unset( $post );

				// Make sure All Day events come first
				$all_day = array();
				$ongoing = array();
				$hourly  = array();
				foreach ( $unsorted_posts as $i => $post ) {
					if ( $post->timeslot == __( 'All Day', 'tribe-events-calendar' ) ) {
						$all_day[$i] = $post;
					} else {
						if ( $post->timeslot == __( 'Ongoing', 'tribe-events-calendar' ) ) {
							$ongoing[$i] = $post;
						} else {
							$hourly[$i] = $post;
						}
					}
				}

				$wp_query->posts = array_values( $all_day + $ongoing + $hourly );
				$wp_query->rewind_posts();
			}
		}

		/**
		 * Set up the notices for this template
		 *
		 * @return void
		 **/
		public function set_notices() {
			global $wp_query;
			$tribe           = TribeEvents::instance();
			$geographic_term = '';
			$search_term     = '';
			$tax_term        = '';

			// No need to set notices unless we didn't find anything
			if ( have_posts() ) {
				return;
			}

			// Do we have a keyword or place name search?
			if ( ! empty( $wp_query->query_vars['s'] ) ) {
				$search_term = $wp_query->query_vars['s'];
			} elseif ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
				$search_term = $_REQUEST['tribe-bar-search'];
			} elseif ( ! empty( $_REQUEST['tribe-bar-geoloc'] ) ) {
				$geographic_term = $_REQUEST['tribe-bar-geoloc'];
			}
			if ( is_tax( $tribe->get_event_taxonomy() ) ) {
				$tax_term = get_term_by( 'slug', get_query_var( 'term' ), $tribe->get_event_taxonomy() );
				$tax_term = $tax_term->name;
			}

			// No events found on this day
			if ( empty( $search_term ) && empty( $geographic_term ) && ! empty( $tax_term ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), $tax_term, date_i18n( tribe_get_date_format( true ), strtotime( get_query_var( 'eventDate' ) ) ) ) );
			} elseif ( empty( $search_term ) && empty( $geographic_term ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( tribe_get_date_format( true ), strtotime( get_query_var( 'eventDate' ) ) ) ) );
			} else {
				parent::set_notices();
			}
		}

		/**
		 * AJAX handler for tribe_event_day (dayview navigation)
		 * This loads up the day view shard with all the appropriate events for the day
		 *
		 * @return void
		 */
		function ajax_response() {
			if ( isset( $_POST['eventDate'] ) && $_POST['eventDate'] ) {

				TribeEventsQuery::init();

				$states[] = 'publish';
				if ( 0 < get_current_user_id() ) {
					$states[] = 'private';
				}

				$args = array(
					'post_status'  => $states,
					'eventDate'    => $_POST["eventDate"],
					'eventDisplay' => 'day'
				);

				TribeEvents::instance()->displaying = 'day';

				if ( isset( $_POST['tribe_event_category'] ) ) {
					$args[TribeEvents::TAXONOMY] = $_POST['tribe_event_category'];
				}

				$query = TribeEventsQuery::getEvents( $args, true );

				global $wp_query, $post;
				$wp_query = $query;

				if ( have_posts() ) {
					the_post(); // TODO: why is this here?
					rewind_posts(); // so we don't skip the first post when rendering
				}

				add_filter( 'tribe_is_day', '__return_true' ); // simplest way to declare that this is a day view

				ob_start();
				tribe_get_view( 'day/content' );

				$response = array(
					'html'        => ob_get_clean(),
					'success'     => true,
					'total_count' => $query->found_posts,
					'view'        => 'day',
				);
				apply_filters( 'tribe_events_ajax_response', $response );

				header( 'Content-type: application/json' );
				echo json_encode( $response );
				die();
			}

		}
	}
}
