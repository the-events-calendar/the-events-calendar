<?php
/**
 * Handles The Events Calendar Context manipulation.
 *
 * @since   4.9.4
 *
 * @package Tribe\Events\Service_Providers
 */

namespace Tribe\Events\Service_Providers;

use Tribe\Events\Views\V2\Template\Settings\Advanced_Display;
use Tribe\Events\Views\V2\Utils;
use Tribe__Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

/**
 * Class Context
 *
 * @since   4.9.4
 *
 * @package Tribe\Events\Service_Providers
 */

use TEC\Common\Contracts\Service_Provider;


class Context extends Service_Provider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.4
	 */
	public function register() {
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );
	}

	/**
	 * Filters the context locations to add the ones used by The Events Calendar.
	 *
	 * @since 4.9.4
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ) {
		$locations = array_merge(
			$locations,
			[
				'event_display'        => [
					'read'  => [
						Tribe__Context::WP_MATCHED_QUERY => [ 'eventDisplay' ],
						Tribe__Context::WP_PARSED        => [ 'eventDisplay' ],
						Tribe__Context::REQUEST_VAR      => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::QUERY_VAR        => 'eventDisplay',
						Tribe__Context::TRIBE_OPTION     => 'viewOption',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::QUERY_VAR   => 'eventDisplay',
					],
				],
				'view'                 => [
					'read'  => [
						Tribe__Context::WP_MATCHED_QUERY => [ 'eventDisplay' ],
						Tribe__Context::WP_PARSED        => [ 'eventDisplay' ],
						Tribe__Context::REQUEST_VAR      => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::QUERY_VAR        => [ 'tribe_view', 'eventDisplay' ],
						Tribe__Context::FUNC             => [
							static function () {
								if ( 1 === (int) tribe_get_request_var( 'ical', 0 ) && is_singular( TEC::POSTTYPE ) ) {
									return 'single-event';
								}

								return Tribe__Context::NOT_FOUND;
							}
						],
						Tribe__Context::TRIBE_OPTION     => 'viewOption',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::QUERY_VAR   => [ 'tribe_view', 'eventDisplay' ],
					],
				],
				'view_data'            => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => [ 'tribe_view_data', 'view_data' ],
						Tribe__Context::QUERY_VAR   => [ 'tribe_view_data', 'view_data' ],
						Tribe__Context::FILTER      => 'tribe_view_data',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'tribe_view_data',
						Tribe__Context::QUERY_VAR   => 'tribe_view_data',
					],
				],
				'event_date'           => [
					'read'  => [
						Tribe__Context::FUNC        => [
							static function () {
								return Utils\View::get_data( 'bar-date', Tribe__Context::NOT_FOUND );
							},
						],
						Tribe__Context::REQUEST_VAR => [ 'eventDate', 'tribe-bar-date' ],
						Tribe__Context::QUERY_VAR   => 'eventDate',
						Tribe__Context::WP_PARSED   => 'eventDate',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => [ 'eventDate', 'tribe-bar-date' ],
						Tribe__Context::QUERY_VAR   => 'eventDate',
					],
				],
				'event_sequence'       => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => 'eventSequence',
						Tribe__Context::QUERY_VAR   => 'eventSequence',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'eventSequence',
						Tribe__Context::QUERY_VAR   => 'eventSequence',
					],
				],
				'ical'                 => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => 'ical',
						Tribe__Context::QUERY_VAR   => 'ical',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'ical',
						Tribe__Context::QUERY_VAR   => 'ical',
					],
				],
				'start_date'           => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => 'start_date',
						Tribe__Context::QUERY_VAR   => 'start_date',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'start_date',
						Tribe__Context::QUERY_VAR   => 'start_date',
					],
				],
				'end_date'             => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => 'end_date',
						Tribe__Context::QUERY_VAR   => 'end_date',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'end_date',
						Tribe__Context::QUERY_VAR   => 'end_date',
					],
				],
				'featured'             => [
					'read'  => [
						Tribe__Context::REQUEST_VAR      => 'featured',
						Tribe__Context::QUERY_VAR        => 'featured',
						Tribe__Context::WP_MATCHED_QUERY => 'featured',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR      => 'featured',
						Tribe__Context::QUERY_VAR        => 'featured',
					],
				],
				TEC::TAXONOMY          => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => TEC::TAXONOMY,
						Tribe__Context::QUERY_VAR   => TEC::TAXONOMY,
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => TEC::TAXONOMY,
						Tribe__Context::QUERY_VAR   => TEC::TAXONOMY,
					],
				],
				'remove_date_filters'  => [
					'read'  => [
						Tribe__Context::REQUEST_VAR => 'tribe_remove_date_filters',
						Tribe__Context::QUERY_VAR   => 'tribe_remove_date_filters',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => 'tribe_remove_date_filters',
						Tribe__Context::QUERY_VAR   => 'tribe_remove_date_filters',
					],
				],
				'event_display_mode'   => [
					/**
					 * We use the `eventDisplay` query var with duplicity: when parsed from the path it represents the View, when
					 * appended as a query var it represents the "view mode". Here we invert the order to read the appended query
					 * var first and get, from its position, a clean variable we can consume in Views.
					 */
					'read' => [
						Tribe__Context::REQUEST_VAR => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::WP_PARSED   => [ 'eventDisplay', 'tribe_event_display' ],
						Tribe__Context::QUERY_VAR   => [ 'eventDisplay', 'tribe_event_display' ],
					],
				],
				'tribe_event_display'   => [
					/**
					 * On V1 we depend on `tribe_event_display` to handle Plain permalink usage of `past` events.
					 * The context need to be aware of where to read and write this from.
					 */
					'read' => [
						Tribe__Context::REQUEST_VAR => [ 'tribe_event_display' ],
						Tribe__Context::WP_PARSED   => [ 'tribe_event_display' ],
						Tribe__Context::QUERY_VAR   => [ 'tribe_event_display' ],
					],
					'write' => [
						Tribe__Context::REQUEST_VAR => [ 'tribe_event_display', 'event_display_mode' ],
						Tribe__Context::QUERY_VAR   => [ 'tribe_event_display' ],
					],
				],
				'keyword'              => [
					'read' => [
						Tribe__Context::FUNC        => [
							static function () {
								return Utils\View::get_data( 'bar-keyword', Tribe__Context::NOT_FOUND );
							},
						],
						Tribe__Context::REQUEST_VAR => [ 's', 'search', 'tribe-bar-search' ],
						Tribe__Context::LOCATION_FUNC => [
							'view_data',
							static function ( $data ) {
								if ( ! is_array( $data ) || empty( $data['tribe-bar-search'] ) ) {
									return Tribe__Context::NOT_FOUND;
								}

								return $data['tribe-bar-search'];
							}
						]
					],
				],
				'events_per_page'      => [
					'read'  => [
						Tribe__Context::REQUEST_VAR  => 'posts_per_page',
						Tribe__Context::TRIBE_OPTION => [ 'posts_per_page', 'postsPerPage' ],
						Tribe__Context::OPTION       => 'posts_per_page',
					],
					'write' => [
						Tribe__Context::REQUEST_VAR  => 'posts_per_page',
						Tribe__Context::TRIBE_OPTION => 'postsPerPage',
					],
				],
				'month_posts_per_page' => [
					'read'  => [
						Tribe__Context::TRIBE_OPTION => 'monthEventAmount',
					],
					'write' => [
						Tribe__Context::TRIBE_OPTION => 'monthEventAmount',
					],
				],
				'today'                => [
					'read' => [
						Tribe__Context::FUNC => static function () {
							return Dates::build_date_object()
										->setTime( 0, 0, 0 )
										->format( Dates::DBDATETIMEFORMAT );
						},
					],
				],
				'now'                  => [
					'read' => [
						Tribe__Context::FUNC => static function () {
							return Dates::build_date_object()->format( Dates::DBDATETIMEFORMAT );
						},
					],
				],
				'start_of_week'        => [
					'read'  => [ Tribe__Context::OPTION => 'start_of_week' ],
					'write' => [ Tribe__Context::OPTION => 'start_of_week' ],
				],
				'tec_post_type'        => [
					'read' => [
						Tribe__Context::FUNC          => [
							static function () {
								$found = [
									! empty( tribe_get_request_var( TEC::POSTTYPE, false ) ),
									! empty( tribe_get_request_var( Venue::POSTTYPE, false ) ),
									! empty( tribe_get_request_var( Organizer::POSTTYPE, false ) ),
								];
								$found = array_filter( $found );

								return count( $found ) ? true : Tribe__Context::NOT_FOUND;
							},
						],
						Tribe__Context::LOCATION_FUNC => [
							'post_type',
							static function ( $post_type ) {
								$found = count(
									array_intersect(
										(array) $post_type,
										[ TEC::POSTTYPE, Venue::POSTTYPE, Organizer::POSTTYPE, ]
									)
								);
								return $found ?: Tribe__Context::NOT_FOUND;
							},
						],
					],
				],
				'event_post_type'      => [
					'read' => [
						Tribe__Context::FUNC          => [
							static function () {
								return ! empty( tribe_get_request_var( TEC::POSTTYPE, false ) ) ?: Tribe__Context::NOT_FOUND;
							},
						],
						Tribe__Context::LOCATION_FUNC => [
							'post_type',
							static function ( $post_type ) {
								return [ TEC::POSTTYPE ] === (array) $post_type ? true : Tribe__Context::NOT_FOUND;
							},
						],
					],
				],
				'venue_post_type'      => [
					'read' => [
						Tribe__Context::FUNC          => [
							static function () {
								return ! empty( tribe_get_request_var( Venue::POSTTYPE, false ) ) ?: Tribe__Context::NOT_FOUND;
							},
						],
						Tribe__Context::LOCATION_FUNC => [
							'post_type',
							static function ( $post_type ) {
								return [ Venue::POSTTYPE ] === (array) $post_type ? true : Tribe__Context::NOT_FOUND;
							},
						],
					],
				],
				'organizer_post_type'  => [
					'read' => [
						Tribe__Context::FUNC          => [
							static function () {
								return ! empty( tribe_get_request_var( Organizer::POSTTYPE, false ) ) ?: Tribe__Context::NOT_FOUND;
							},
						],
						Tribe__Context::LOCATION_FUNC => [
							'post_type',
							static function ( $post_type ) {
								return [ Organizer::POSTTYPE ] === (array) $post_type ? true : Tribe__Context::NOT_FOUND;
							},
						],
					],
				],
				'event_category'       => [
					'read' => [
						Tribe__Context::QUERY_PROP  => [ TEC::TAXONOMY ],
						Tribe__Context::QUERY_VAR   => [ TEC::TAXONOMY ],
						Tribe__Context::REQUEST_VAR => [ TEC::TAXONOMY ],
					],
				],
				'view_url'             => [
					'read' => [
						Tribe__Context::FUNC          => [
							static function () {
								return Utils\View::get_data( 'url', Tribe__Context::NOT_FOUND );
							},
						],
						Tribe__Context::LOCATION_FUNC => [
							// Handles the datepicker request.
							'view_data',
							static function ( $data ) {
								$date_k = 'tribe-bar-date';

								return is_array( $data ) && isset( $data[ $date_k ] )
									? add_query_arg( [ $date_k => $data[ $date_k ] ], tribe_get_request_var( 'url', home_url() ) )
									: Tribe__Context::NOT_FOUND;
							},
						],
					],
				],
				'view_prev_url'        => [
					'read' => [
						Tribe__Context::FUNC => [
							static function () {
								return Utils\View::get_data( 'prev_url', Tribe__Context::NOT_FOUND );
							},
							static function () {
								// Handles the datepicker request.
								return tribe_get_request_var( 'url', Tribe__Context::NOT_FOUND );
							},
						],
					],
				],
				'view_request'         => [
					'read'  => [
						Tribe__Context::WP_MATCHED_QUERY => [ 'eventDisplay' ],
						Tribe__Context::WP_PARSED        => [ 'eventDisplay' ],
						Tribe__Context::REQUEST_VAR      => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
						Tribe__Context::QUERY_VAR        => [ 'tribe_view', 'eventDisplay' ],
					],
				],
				'latest_event_date'    => [
					'read'  => [
						Tribe__Context::TRIBE_OPTION => [ 'latest_date' ],
					],
					'write' => [
						Tribe__Context::TRIBE_OPTION => [ 'latest_date' ],
					],
				],
				'earliest_event_date'  => [
					'read'  => [
						Tribe__Context::TRIBE_OPTION => [ 'earliest_date' ],
					],
					'write' => [
						Tribe__Context::TRIBE_OPTION => [ 'earliest_date' ],
					],
				],
			]
		);

		return $locations;
	}
}
