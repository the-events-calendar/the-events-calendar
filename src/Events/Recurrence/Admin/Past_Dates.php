<?php
/**
 * Bounded, authenticated access to an event's past dates.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Controller;
use TEC\Events\Recurrence\Occurrences_List;
use WP_REST_Request;
use WP_REST_Response;

/** Past-date editor pagination. @since TBD */
class Past_Dates {
	/** Registers the read-only editor route. @since TBD @return void */
	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'routes' ] );
	}

	/** Removes the route-registration callback. @since TBD @return void */
	public function unregister(): void {
		remove_action( 'rest_api_init', [ $this, 'routes' ] );
	}

	/** Registers a capability-protected route with a fixed page size. @since TBD @return void */
	public function routes(): void {
		register_rest_route(
			'tec/v1',
			'/events/(?P<id>\d+)/past-dates',
			[
				'methods'             => 'GET',
				'permission_callback' => [ $this, 'can_read' ],
				'callback'            => [ $this, 'read' ],
				'args'                => [
					'id'     => [
						'type'    => 'integer',
						'minimum' => 1,
					],
					'offset' => [
						'type'    => 'integer',
						'minimum' => 0,
						'default' => 0,
					],
					'as_of'  => [
						'type'     => 'integer',
						'minimum'  => 1,
						'required' => true,
					],
				],
			]
		);
	}

	/**
	 * @since TBD
	 * @param WP_REST_Request $request Request.
	 * @return bool Whether its parent event can be edited.
	 */
	public function can_read( WP_REST_Request $request ): bool {
		if ( ! Controller::provides_occurrences() ) {
			return false;
		}
		$id = Occurrence::normalize_id( (int) $request['id'] );
		return 'tribe_events' === get_post_type( $id ) && current_user_can( 'edit_post', $id );
	}

	/**
	 * @since TBD
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response A single page of past dates, never publicly cached.
	 */
	public function read( WP_REST_Request $request ): WP_REST_Response {
		$response = new WP_REST_Response( $this->page( (int) $request['id'], (int) $request['offset'], min( time(), (int) $request['as_of'] ) ) );
		$response->header( 'Cache-Control', 'private, no-store' );
		return $response;
	}

	/**
	 * @since TBD
	 * @param int $id Event ID.
	 * @param int $offset Loaded rows.
	 * @param int $as_of UTC boundary.
	 * @return array Page.
	 */
	public function page( int $id, int $offset, int $as_of ): array {
		$id    = Occurrence::normalize_id( $id );
		$total = (int) Occurrence::where( 'post_id', $id )->where( 'end_date_utc', '<', gmdate( 'Y-m-d H:i:s', $as_of ) )->count();
		$list  = tribe( Occurrences_List::class );
		$dates = array_map( [ $list, 'format_chip' ], $list->get_scheduled_dates( $id, 'past', 25, $offset, $as_of ) );
		return [
			'dates' => $dates,
			'total' => $total,
			'next'  => $offset + count( $dates ) < $total ? $offset + count( $dates ) : null,
		];
	}

	/**
	 * @since TBD
	 * @param int $id Event ID.
	 * @return array Initial chips, bounded to 25 for past-only events.
	 */
	public function summary( int $id ): array {
		$id       = Occurrence::normalize_id( $id );
		$as_of    = time();
		$upcoming = (int) Occurrence::where( 'post_id', $id )->where( 'end_date_utc', '>=', gmdate( 'Y-m-d H:i:s', $as_of ) )->count();
		$list     = tribe( Occurrences_List::class );
		if ( ! $upcoming ) {
			$page = $this->page( $id, 0, $as_of );
		} else {
			$dates = array_map( [ $list, 'format_chip' ], $list->get_scheduled_dates( $id ) );
			$page  = [
				'dates' => $dates,
				'total' => count( $dates ),
				'next'  => null,
			];
		}
		return [
			'count'    => $page['total'],
			'dates'    => $page['dates'],
			'next'     => $page['next'],
			'asOf'     => $as_of,
			'pastOnly' => ! $upcoming,
			'url'      => rest_url( 'tec/v1/events/' . $id . '/past-dates' ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		];
	}
}
