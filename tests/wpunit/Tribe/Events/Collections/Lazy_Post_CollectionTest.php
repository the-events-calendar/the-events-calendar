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
	 * A collection read back from the object cache can be written to it again: the second round trip has to
	 * carry the same callback as the first one, or the collection comes back with nothing to rebuild from.
	 *
	 * @test
	 */
	public function it_should_survive_a_second_round_trip() {
		$post     = static::factory()->post->create_and_get();
		$restored = unserialize( serialize( $this->make_collection( 'get_post', [ $post ] ) ) );

		$serialized = serialize( $restored );
		$this->assertStringContainsString( 's:8:"get_post"', $serialized );

		$restored_again = unserialize( $serialized );
		$items          = $restored_again->all;

		$this->assertCount( 1, $items );
		$this->assertInstanceOf( \WP_Post::class, $items[0] );
		$this->assertSame( $post->ID, $items[0]->ID );
	}

	/**
	 * @test
	 */
	public function it_should_allow_filtering_the_allowed_callbacks() {
		$serialized = serialize( $this->make_collection( 'get_post', [ static::factory()->post->create_and_get() ] ) );
		// Swap the allowed callback for one that is not on the list.
		$tampered = str_replace( 's:8:"get_post"', 's:14:"__return_false"', $serialized );

		$items = new \ReflectionProperty( Lazy_Post_Collection::class, 'items' );
		$items->setAccessible( true );

		$this->assertNull( $items->getValue( unserialize( $tampered ) ) );

		add_filter(
			'tec_events_lazy_post_collection_allowed_unserialize_callbacks',
			static function ( array $callbacks ): array {
				$callbacks[] = '__return_false';

				return $callbacks;
			}
		);

		$this->assertSame( [ false ], $items->getValue( unserialize( $tampered ) ) );
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
