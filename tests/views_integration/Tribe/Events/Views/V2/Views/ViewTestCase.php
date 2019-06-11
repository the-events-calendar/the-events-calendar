<?php
/**
 *
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2\Views;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Test\Traits\With_Post_Remapping;
use Tribe\Events\Views\V2\TestCase;

class ViewTestCase extends TestCase {

	use With_Post_Remapping;

	/**
	 * In the `reset_post_dates` methods all date-related post fields will be set to this value.
	 *
	 * @var string
	 */
	protected $mock_post_date = '2019-01-01 09:00:00';

	/**
	 * When mocking the `date` function this is the value that will be used to generate the date in place of the real
	 * one.
	 *
	 * @var string
	 */
	protected $mock_date_value = '2019-01-01 09:00:00';

	/**
	 * Sets up the View test context mocking some commonly used functions and setting up the code to filter some time,
	 * or date, dependant values to keep the snapshots consistent across time.
	 */
	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Mock calls to the date function to return a fixed value.
		Test::replace( 'date', function ( $format ) {
			return ( new \DateTime( $this->mock_date_value, new \DateTimeZone( 'UTC' ) ) )
				->format( $format );
		} );
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * Sets the date/time-dependant fields of a post to a fixed value.
	 *
	 * @param \WP_Post $post The post opbject to modify.
	 *
	 * @return \WP_Post The modified post object.
	 */
	public function reset_post_dates( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return $post;
		}

		foreach ( [ 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ] as $field ) {
			$post->{$field} = $this->mock_post_date;
		}

		return $post;
	}

	/**
	 * Tears down the View test context having care to reset function mocks too.
	 */
	public function tearDown() {
		Test::tearDown();
		parent::tearDown();
	}

	/**
	 * Sets the date/time-dependant fields of an array of posts to a fixed value.
	 *
	 * @param \WP_Post[] $post The post opbjects to modify.
	 *
	 * @return \WP_Post[] The modified posts object.
	 */
	public function reset_posts_dates( $posts ) {
		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return $posts;
		}
		array_walk( $posts, [ $this, 'reset_post_dates' ] );

		return $posts;
	}
}
