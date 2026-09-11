<?php

namespace Tribe\Events\Views\V2\Widgets;

use Codeception\TestCase\WPTestCase;

class Service_ProviderTest extends WPTestCase {

	protected function make_block( string $serialized ): array {
		return [
			'attrs' => [
				'idBase'   => 'tribe-widget-events-list',
				'instance' => [
					'encoded' => base64_encode( $serialized ),
					'hash'    => 'untrusted',
				],
			],
		];
	}

	protected function render( array $block ): array {
		$provider = new Service_Provider( tribe() );

		return $provider->enable_rendering_widget_copied( $block );
	}

	/**
	 * @test
	 */
	public function it_should_re_hash_a_plain_data_instance() {
		$serialized = serialize( [ 'title' => 'Upcoming', 'limit' => 5 ] );
		$block      = $this->render( $this->make_block( $serialized ) );

		$this->assertSame( wp_hash( $serialized ), $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_not_re_hash_an_object_instance() {
		$serialized = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';
		$block      = $this->render( $this->make_block( $serialized ) );

		$this->assertSame( 'untrusted', $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_not_re_hash_a_nested_object_instance() {
		$serialized = serialize( [ 'settings' => [ new \stdClass() ] ] );
		$block      = $this->render( $this->make_block( $serialized ) );

		$this->assertSame( 'untrusted', $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_not_re_hash_an_object_instance_with_a_malformed_tail() {
		// Malformed input must not be re-signed.
		$serialized_object = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';
		$serialized = 'a:2:{i:0;' . $serialized_object . 'i:1;X}';
		$block      = $this->render( $this->make_block( $serialized ) );

		$this->assertSame( 'untrusted', $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_store_object_free_data_for_a_crafted_instance() {
		$serialized_object = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';
		$serialized = 'a:2:{i:0;' . $serialized_object . 'i:0;s:2:"ok";}';
		$block      = $this->render( $this->make_block( $serialized ) );
		$instance   = $block['attrs']['instance'];
		$decoded    = base64_decode( $instance['encoded'] );

		// Stored data is object-free and its hash matches the stored bytes.
		$this->assertStringNotContainsString( 'O:', $decoded );
		$this->assertSame( wp_hash( $decoded ), $instance['hash'] );
		$this->assertSame( [ 'ok' ], unserialize( $decoded, [ 'allowed_classes' => false ] ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_re_sign_a_crafted_unsafe_instance() {
		$serialized_object = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';
		$serialized = 'a:2:{i:0;s:2:"ok";i:0;' . $serialized_object . '}';
		$block      = $this->render( $this->make_block( $serialized ) );

		$this->assertSame( 'untrusted', $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_re_sign_a_plain_instance_to_its_canonical_form() {
		// Non-canonical input must be stored in canonical form.
		$serialized = 'a:2:{i:0;s:1:"a";i:0;s:1:"b";}';
		$block      = $this->render( $this->make_block( $serialized ) );
		$instance   = $block['attrs']['instance'];
		$decoded    = base64_decode( $instance['encoded'] );

		$this->assertSame( 'a:1:{i:0;s:1:"b";}', $decoded );
		$this->assertSame( wp_hash( $decoded ), $instance['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_reject_untrusted_input_without_crashing() {
		$block = $this->render( $this->make_block( 'a:1:{i:0;R:1;}' ) );

		$this->assertSame( 'untrusted', $block['attrs']['instance']['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_ignore_blocks_from_other_widgets() {
		$block = $this->make_block( serialize( [ 'title' => 'x' ] ) );
		$block['attrs']['idBase'] = 'some-other-widget';

		$result = $this->render( $block );

		$this->assertSame( 'untrusted', $result['attrs']['instance']['hash'] );
	}
}
