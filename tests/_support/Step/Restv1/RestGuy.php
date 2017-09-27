<?php

namespace Step\Restv1;

class RestGuy extends \Restv1Tester {

	use Auth;

	/**
	 * Asserts that a JSON response contains the expected URL at the specified index.
	 *
	 * @param string $index
	 * @param string $url
	 */
	public function see_response_contains_url( $index, $url ) {
		$response = $this->grabResponse();
		$decoded = json_decode( $response );
		$components = parse_url( $url );

		if ( isset( $components['path'] ) ) {
			$components['path'] = trim( $components['path'], '/' );
		}

		if ( false === $components ) {
			throw new \InvalidArgumentException( "Could not parse URL {$url}" );
		}

		if ( empty( $decoded->{$index} ) ) {
			$this->fail( "Response JSON does not contain the {$index} key" );
		}

		$response_url = $decoded->{$index};
		$found = parse_url( $response_url );

		if ( false === $found ) {
			$this->fail( "Response JSON does contain the {$index} key, but it is a malformed URL ({$response_url})" );
		}

		$response_components = parse_url( $response_url );

		if ( isset( $response_components['path'] ) ) {
			$response_components['path'] = trim( $response_components['path'], '/' );
		}

		$intersected = array_intersect_key( $response_components, $components );

		if ( count( $intersected ) !== count( $components ) ) {
			$this->fail( "Response JSON does contain the {$index} key, but " );
		}
		foreach ( $components as $key => $value ) {
			$this->assertArrayHasKey( $key, $response_components );
			if ( $key === 'query' ) {
				$this->assertEquals( parse_str( $response_components[ $key ] ), parse_str( $value ) );
			} else {
				$this->assertEquals( $response_components[ $key ], $value );
			}
		}
	}
}
