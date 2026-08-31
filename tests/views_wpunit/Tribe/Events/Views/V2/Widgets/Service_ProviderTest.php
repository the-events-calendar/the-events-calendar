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
		// Object at index 0, then an invalid token so the guard's pre-parse returns false.
		$gadget     = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';
		$serialized = 'a:2:{i:0;' . $gadget . 'i:1;X}';
		$block      = $this->render( $this->make_block( $serialized ) );

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
