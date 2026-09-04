<?php

namespace TEC\Events\Custom_Tables\V1\Events\Provisional;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

/**
 * Ported from the Events Calendar Pro suite together with the class: meta writes against
 * a provisional post ID are retargeted to the real Event post.
 */
class MetaTest extends WPTestCase {
	use With_Recurrence_Engine;

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Provisional Meta Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-05 09:00:00',
				'duration'   => HOUR_IN_SECONDS,
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	private function provisional_id_of( WP_Post $post ): int {
		$occurrence = Occurrence::find_by_post_id( $post->ID );
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		return (int) $occurrence->provisional_id;
	}

	/**
	 * @test
	 */
	public function should_return_the_check_when_not_null(): void {
		$post = $this->given_an_event();

		$check = tribe( Meta::class )->update_metadata( 'test', $this->provisional_id_of( $post ), 'custom_key', 'whatever', 'another' );

		$this->assertEquals( 'test', $check );
		$this->assertEquals( '', get_post_meta( $post->ID, 'custom_key', true ) );
	}

	/**
	 * @test
	 */
	public function should_update_the_real_post_meta_for_a_provisional_id(): void {
		$post = $this->given_an_event();
		$this->assertEquals( '', get_post_meta( $post->ID, 'custom_key', true ) );

		tribe( Meta::class )->update_metadata( null, $this->provisional_id_of( $post ), 'custom_key', 'whatever', 'another' );

		$this->assertEquals( 'whatever', get_post_meta( $post->ID, 'custom_key', true ) );
	}

	public function existing_value_data_provider(): array {
		return [
			'same value'      => [ 'whatever' ],
			'different value' => [ 'diffvalue' ],
			'empty string'    => [ '' ],
		];
	}

	/**
	 * @dataProvider existing_value_data_provider
	 * @test
	 */
	public function should_update_the_real_post_meta_with_an_existing_value( string $prev_value ): void {
		$post = $this->given_an_event();
		update_post_meta( $post->ID, 'custom_key', 'whatever' );

		tribe( Meta::class )->update_metadata( null, $this->provisional_id_of( $post ), 'custom_key', 'whatever', $prev_value );

		$this->assertEquals( 'whatever', get_post_meta( $post->ID, 'custom_key', true ) );
	}

	/**
	 * @test
	 */
	public function should_do_nothing_for_a_real_post_id(): void {
		$post = $this->given_an_event();
		$this->assertEquals( '', get_post_meta( $post->ID, 'custom_key', true ) );

		$check = tribe( Meta::class )->update_metadata( null, $post->ID, 'custom_key', 'whatever', 'diffvalue' );

		$this->assertNull( $check );
		$this->assertEquals( '', get_post_meta( $post->ID, 'custom_key', true ) );
	}

	/**
	 * It should retarget the WordPress meta API calls made against a provisional id
	 *
	 * @test
	 */
	public function should_retarget_update_post_meta_calls_made_against_a_provisional_id(): void {
		$post = $this->given_a_multi_date_event();
		$this->assertEquals( '', get_post_meta( $post->ID, 'shared_key', true ) );

		$second = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date_utc', 'DESC' )->first();

		update_post_meta( (int) $second->provisional_id, 'shared_key', 'from the occurrence' );

		$this->assertEquals( 'from the occurrence', get_post_meta( $post->ID, 'shared_key', true ) );
	}
}
