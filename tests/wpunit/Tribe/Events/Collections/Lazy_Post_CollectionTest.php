<?php

namespace Tribe\Events\Collections;

class Lazy_Post_CollectionTest extends \Codeception\TestCase\WPTestCase {

	protected function make_collection( string $callback, array $items ): Lazy_Post_Collection {
		$collection = new Lazy_Post_Collection(
			static function () use ( $items ): array {
				return $items;
			},
			$callback
		);
		$collection->all; // Resolve so the items are cached before serialization.

		return $collection;
	}

	/**
	 * @test
	 */
	public function it_should_round_trip_with_an_allowed_callback() {
		$post       = static::factory()->post->create_and_get();
		$serialized = serialize( $this->make_collection( 'get_post', [ $post ] ) );

		$restored = unserialize( $serialized );
		$items    = $restored->all;

		$this->assertCount( 1, $items );
		$this->assertInstanceOf( \WP_Post::class, $items[0] );
		$this->assertSame( $post->ID, $items[0]->ID );
	}

	/**
	 * @test
	 */
	public function it_should_reject_a_disallowed_callback() {
		$serialized = serialize( $this->make_collection( 'get_post', [ static::factory()->post->create_and_get() ] ) );
		// Swap the allowed callback for an arbitrary function.
		$tampered = str_replace( 's:8:"get_post"', 's:6:"system"', $serialized );

		$restored = unserialize( $tampered );

		$items = new \ReflectionProperty( Lazy_Post_Collection::class, 'items' );
		$items->setAccessible( true );

		$this->assertNull( $items->getValue( $restored ) );
	}
}
