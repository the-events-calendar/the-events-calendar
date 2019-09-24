<?php
/**
 *
 *
 * @since   4.9.4
 * @package Tribe\Events\Service_Providers
 */

namespace Tribe\Events\Service_Providers;

use Tribe\Events\Views\V2\Utils;
use Tribe__Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

class Context extends \tad_DI52_ServiceProvider {

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
		$locations = array_merge( $locations, [
			'event_display'               => [
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
			'view'                        => [
				'read'  => [
					Tribe__Context::WP_MATCHED_QUERY => [ 'eventDisplay' ],
					Tribe__Context::WP_PARSED        => [ 'eventDisplay' ],
					Tribe__Context::REQUEST_VAR      => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
					Tribe__Context::QUERY_VAR        => [ 'tribe_view', 'eventDisplay' ],
					Tribe__Context::TRIBE_OPTION     => 'viewOption',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
					Tribe__Context::QUERY_VAR   => [ 'tribe_view', 'eventDisplay' ],
				],
			],
			'view_data'                   => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'tribe_view_data',
					Tribe__Context::QUERY_VAR   => 'tribe_view_data',
					Tribe__Context::FILTER      => 'tribe_view_data',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'tribe_view_data',
					Tribe__Context::QUERY_VAR   => 'tribe_view_data',
				],
			],
			'event_date'                  => [
				'read'  => [
					Tribe__Context::FUNC => [
						static function () {
							return Utils\View::get_data( 'bar-date', Tribe__Context::NOT_FOUND );
						},
					],
					Tribe__Context::REQUEST_VAR => [ 'eventDate', 'tribe-bar-date' ],
					Tribe__Context::QUERY_VAR   => 'eventDate',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => [ 'eventDate', 'tribe-bar-date' ],
					Tribe__Context::QUERY_VAR   => 'eventDate',
				],
			],
			'event_sequence'              => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'eventSequence',
					Tribe__Context::QUERY_VAR   => 'eventSequence',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'eventSequence',
					Tribe__Context::QUERY_VAR   => 'eventSequence',
				],
			],
			'ical'                        => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'ical',
					Tribe__Context::QUERY_VAR   => 'ical',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'ical',
					Tribe__Context::QUERY_VAR   => 'ical',
				],
			],
			'start_date'                  => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'start_date',
					Tribe__Context::QUERY_VAR   => 'start_date',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'start_date',
					Tribe__Context::QUERY_VAR   => 'start_date',
				],
			],
			'end_date'                    => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'end_date',
					Tribe__Context::QUERY_VAR   => 'end_date',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'end_date',
					Tribe__Context::QUERY_VAR   => 'end_date',
				],
			],
			'featured'                    => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'featured',
					Tribe__Context::QUERY_VAR   => 'featured',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'featured',
					Tribe__Context::QUERY_VAR   => 'featured',
				],
			],
			TEC::TAXONOMY => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => TEC::TAXONOMY,
					Tribe__Context::QUERY_VAR   => TEC::TAXONOMY,
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => TEC::TAXONOMY,
					Tribe__Context::QUERY_VAR   => TEC::TAXONOMY,
				],
			],
			'remove_date_filters'         => [
				'read'  => [
					Tribe__Context::REQUEST_VAR => 'tribe_remove_date_filters',
					Tribe__Context::QUERY_VAR   => 'tribe_remove_date_filters',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'tribe_remove_date_filters',
					Tribe__Context::QUERY_VAR   => 'tribe_remove_date_filters',
				],
			],
			'event_display_mode'          => [
				/**
				 * We use the `eventDisplay` query var with duplicity: when parsed from the path it represents the View, when
				 * appended as a query var it represents the "view mode". Here we invert the order to read the appended query
				 * var first and get, from its position, a clean variable we can consume in Views.
				 */
				'read' => [
					Tribe__Context::REQUEST_VAR => [ 'view', 'tribe_view', 'tribe_event_display', 'eventDisplay' ],
					Tribe__Context::WP_PARSED   => [ 'eventDisplay' ],
					Tribe__Context::QUERY_VAR   => 'eventDisplay',
				],
			],
			'keyword' => [
				'read' => [
					Tribe__Context::FUNC        => [
						static function () {
							return Utils\View::get_data( 'bar-keyword', Tribe__Context::NOT_FOUND );
						},
					],
					Tribe__Context::REQUEST_VAR => [ 's', 'search', 'tribe-bar-search' ],
				],
			],
			'events_per_page' => [
				'read'  => [
					Tribe__Context::REQUEST_VAR  => 'posts_per_page',
					Tribe__Context::TRIBE_OPTION => [ 'posts_per_page', 'postsPerPage' ],
					Tribe__Context::OPTION       => 'posts_per_page',
				],
				'write' => [
					Tribe__Context::REQUEST_VAR => 'posts_per_page',
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
			'today' => [
				'read' => [
					Tribe__Context::FUNC => static function () {
						return Dates::build_date_object()
						            ->setTime( 0, 0, 0 )
						            ->format( Dates::DBDATETIMEFORMAT );
					}
				],
			],
			'now'   => [
				'read' => [
					Tribe__Context::FUNC => static function () {
						return Dates::build_date_object()->format( Dates::DBDATETIMEFORMAT );
					},
				],
			],
			'start_of_week' => [
				'read'  => [ Tribe__Context::OPTION => 'start_of_week' ],
				'write' => [ Tribe__Context::OPTION => 'start_of_week' ],
			],
			'tec_post_type' => [
				'read' => [
					Tribe__Context::LOCATION_FUNC => [
						'post_type',
						static function ( $post_type ) {
							return count( array_intersect(
									(array) $post_type,
									[ TEC::POSTTYPE, Venue::POSTTYPE, Organizer::POSTTYPE ] )
							);
						}
					],
				],
			],
			'event_post_type' => [
				'read' => [
					Tribe__Context::LOCATION_FUNC => [
						'post_type',
						static function ( $post_type ) {
							return (array) $post_type === [ TEC::POSTTYPE ];
						}
					]
				]
			],
			'venue_post_type' => [
				'read' => [
					Tribe__Context::LOCATION_FUNC => [
						'post_type',
						static function ( $post_type ) {
							return (array) $post_type === [ Venue::POSTTYPE ];
						}
					]
				]
			],
			'organizer_post_type' => [
				'read' => [
					Tribe__Context::LOCATION_FUNC => [
						'post_type',
						static function ( $post_type ) {
							return (array) $post_type === [ Organizer::POSTTYPE ];
						}
					]
				]
			],
			'event_category' => [
				'read' => [
					Tribe__Context::QUERY_PROP  => [ TEC::TAXONOMY ],
					Tribe__Context::QUERY_VAR   => [ TEC::TAXONOMY ],
					Tribe__Context::REQUEST_VAR => [ TEC::TAXONOMY ],
				],
			],
		] );

		return $locations;
	}
}
