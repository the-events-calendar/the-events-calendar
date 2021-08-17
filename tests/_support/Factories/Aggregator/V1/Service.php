<?php

namespace Tribe\Events\Test\Factories\Aggregator\V1;


class Service extends \WP_UnitTest_Factory {

	/**
	 * Builds a mock origins response as sent from the EA server.
	 *
	 * @param array $args                   An array of arguments to override the default values.
	 *                                      `oauth` to override the `oauth` section of the response.
	 *                                      `limit` to override the `limit` section of the response.
	 *                                      `usage` to override the `usage->import` section of the response.
	 *                                      `enable` - a safe list of source ids that will be enabled; defaults to all sources
	 *                                      enabled.
	 *                                      `disable` - a list of blocked source ids that will be disabled; defaults to no sources
	 *                                      blocked.
	 *
	 * @return \stdClass
	 */
	public function create_origins( $args = [] ) {
		$oauth = [
			'eventbrite' => false,
			'facebook'   => true,
			'meetup'     => false,
		];

		if ( isset( $args['oauth'] ) ) {
			$oauth = array_merge( $oauth, $args['oauth'] );
		}

		$limit = [
			'import' => 100,
		];

		if ( isset( $args['limit'] ) ) {
			$limit = array_merge( $limit, $args['limit'] );
		}

		$usage = [
			'used'      => 0,
			'remaining' => 100,
		];

		if ( isset( $args['usage'] ) ) {
			$usage = array_merge( $usage, $args['usage'] );
		}

		$origin = [
			0 => set_object_state( [
				'id'   => 'ics',
				'name' => 'ICS File',
			] ),
			1 => set_object_state( [
				'id'   => 'facebook',
				'name' => 'Facebook',
			] ),
			2 => set_object_state( [
				'id'   => 'ical',
				'name' => 'iCalendar',
			] ),
			3 => set_object_state( [
				'id'   => 'gcal',
				'name' => 'Google Calendar',
			] ),
			4 => set_object_state( [
				'id'   => 'meetup',
				'name' => 'Meetup',
			] ),
			5 => set_object_state( [
				'id'   => 'url',
				'name' => 'Other URL',
			] ),
		];

		$sources_safe_list = isset( $args['enable'] ) ? (array) $args['enable'] : false;
		if ( is_array( $sources_safe_list ) ) {
			$origin = array_filter( $origin, function ( $origin ) use ( $sources_safe_list ) {
				return in_array( $origin->id, $sources_safe_list );
			} );
		}

		$sources_block_list = isset( $args['disable'] ) ? (array) $args['disable'] : false;
		if ( is_array( $sources_block_list ) ) {
			$origin = array_filter( $origin, function ( $origin ) use ( $sources_block_list ) {
				return ! in_array( $origin->id, $sources_block_list );
			} );
		}

		$response = set_object_state( [
			'origin' => $origin,
			'oauth'  => set_object_state( $oauth ),
			'limit'  => set_object_state( $limit ),
			'usage'  => set_object_state( [
				'import' => set_object_state( $usage ),
			] ),
		] );


		return $response;
	}
}
