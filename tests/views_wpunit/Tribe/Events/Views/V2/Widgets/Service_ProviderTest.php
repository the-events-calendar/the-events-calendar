<?php

namespace Tribe\Events\Views\V2\Widgets;

use Codeception\TestCase\WPTestCase;
use Generator;
use stdClass;
use WP_REST_Request;

class Service_ProviderTest extends WPTestCase {
	private const OBJECT_INSTANCE = 'O:45:"Tribe\Events\Collections\Lazy_Post_Collection":2:{s:8:"callback";s:6:"system";s:3:"ids";a:1:{i:0;s:2:"id";}}';

	private const HANDLER_INSTANCE = [
		'limit'                => 1,
		'featured_events_only' => true,
		'no_upcoming_events'   => false,
		'attributes'           => [ 'onmouseover' => 'alert(document.domain)' ],
	];

	/**
	 * @test
	 */
	public function it_should_render_a_correctly_signed_instance() {
		$serialized = serialize( [ 'limit' => 1, 'featured_events_only' => true, 'no_upcoming_events' => false ] );

		$html = do_blocks( $this->legacy_widget_block( $serialized, wp_hash( $serialized ) ) );

		$this->assertStringContainsString( 'tribe-events-c-messages', $html );
	}

	/**
	 * @test
	 */
	public function it_should_not_render_a_forged_legacy_widget_block() {
		$serialized = serialize( self::HANDLER_INSTANCE );

		$html = do_blocks( $this->legacy_widget_block( $serialized, 'invalid-test-signature' ) );

		$this->assertStringNotContainsString( 'onmouseover', $html );
		$this->assertSame( '', trim( $html ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_re_sign_block_data_on_render() {
		$block = [
			'blockName' => 'core/legacy-widget',
			'attrs'     => [
				'idBase'   => 'tribe-widget-events-list',
				'instance' => [
					'encoded' => base64_encode( serialize( [ 'title' => 'Upcoming' ] ) ),
					'hash'    => 'untrusted',
				],
			],
		];

		$this->assertSame( $block, apply_filters( 'render_block_data', $block, $block, null ) );
	}

	/**
	 * @test
	 */
	public function it_should_return_the_block_unchanged_from_the_deprecated_render_callback() {
		$this->setExpectedDeprecated( Service_Provider::class . '::enable_rendering_widget_copied' );
		$block = [ 'attrs' => [ 'idBase' => 'tribe-widget-events-list', 'instance' => [ 'encoded' => 'e30=', 'hash' => 'untrusted' ] ] ];

		$this->assertSame( $block, ( new Service_Provider( tribe() ) )->enable_rendering_widget_copied( $block ) );
	}

	/**
	 * @test
	 */
	public function it_should_leave_a_correctly_signed_encode_request_untouched() {
		$serialized = serialize( [ 'title' => 'Upcoming', 'limit' => 3, 'attributes' => [ 'id' => 'kept' ] ] );

		$request = $this->encode_request( $serialized, wp_hash( $serialized ) );

		$this->assertSame( base64_encode( $serialized ), $request->get_param( 'instance' )['encoded'] );
		$this->assertSame( wp_hash( $serialized ), $request->get_param( 'instance' )['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_drop_keys_that_are_not_widget_settings_from_a_forged_encode_request() {
		$request  = $this->encode_request( serialize( self::HANDLER_INSTANCE ) );
		$instance = $request->get_param( 'instance' );
		$decoded  = $this->decoded_instance( $request );

		$this->assertArrayNotHasKey( 'attributes', $decoded );
		$this->assertSame( self::HANDLER_INSTANCE['limit'], $decoded['limit'] );
		$this->assertTrue( $decoded['featured_events_only'] );
		$this->assertSame( wp_hash( base64_decode( $instance['encoded'] ) ), $instance['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_sanitize_the_values_of_a_forged_encode_request() {
		$decoded = $this->decoded_instance( $this->encode_request( serialize( [ 'title' => '<img src=x onerror=alert(1)>Upcoming' ] ) ) );

		$this->assertSame( 'Upcoming', $decoded['title'] );
	}

	/**
	 * @test
	 */
	public function it_should_drop_keys_that_are_not_widget_settings_from_a_forged_widgets_request() {
		$request = new WP_REST_Request( 'PUT', '/wp/v2/widgets/tribe-widget-events-list-2' );
		$request->set_param( 'id_base', 'tribe-widget-events-list' );
		$request->set_param(
			'instance',
			[
				'encoded' => base64_encode( serialize( self::HANDLER_INSTANCE ) ),
				'hash'    => 'untrusted',
			]
		);

		( new Service_Provider( tribe() ) )->enable_saving_widget_copied( null, $request, '/wp/v2/widgets/tribe-widget-events-list-2' );

		$this->assertArrayNotHasKey( 'attributes', $this->decoded_instance( $request ) );
	}

	public function unsafe_instance_provider(): Generator {
		yield 'object' => [ self::OBJECT_INSTANCE ];
		yield 'nested object' => [ serialize( [ 'settings' => [ new stdClass() ] ] ) ];
		yield 'object with a malformed tail' => [ 'a:2:{i:0;' . self::OBJECT_INSTANCE . 'i:1;X}' ];
		yield 'object after a duplicate key' => [ 'a:2:{i:0;s:2:"ok";i:0;' . self::OBJECT_INSTANCE . '}' ];
		yield 'self reference' => [ 'a:1:{i:0;R:1;}' ];
		yield 'not serialized' => [ 'plain text' ];
	}

	/**
	 * @test
	 * @dataProvider unsafe_instance_provider
	 */
	public function it_should_not_re_sign_an_unsafe_encode_request( string $serialized ) {
		$request = $this->encode_request( $serialized );

		$this->assertSame( 'untrusted', $request->get_param( 'instance' )['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_store_object_free_data_for_a_crafted_encode_request() {
		// The duplicate key replaces the object, so the decoded array is plain.
		$request = $this->encode_request( 'a:2:{i:0;' . self::OBJECT_INSTANCE . 'i:0;s:2:"ok";}' );
		$decoded = base64_decode( $request->get_param( 'instance' )['encoded'] );

		$this->assertStringNotContainsString( 'O:', $decoded );
		$this->assertSame( wp_hash( $decoded ), $request->get_param( 'instance' )['hash'] );
	}

	/**
	 * @test
	 */
	public function it_should_ignore_requests_for_other_widgets() {
		$request = new WP_REST_Request( 'POST', '/wp/v2/widget-types/some-other-widget/encode' );
		$request->set_param( 'instance', [ 'encoded' => base64_encode( serialize( [ 'title' => 'x' ] ) ), 'hash' => 'untrusted' ] );

		( new Service_Provider( tribe() ) )->enable_widget_copy_paste( null, rest_get_server(), $request );

		$this->assertSame( 'untrusted', $request->get_param( 'instance' )['hash'] );
	}

	private function legacy_widget_block( string $serialized, string $hash ): string {
		$attrs = [
			'idBase'   => 'tribe-widget-events-list',
			'instance' => [
				'encoded' => base64_encode( $serialized ),
				'hash'    => $hash,
			],
		];

		return '<!-- wp:legacy-widget ' . wp_json_encode( $attrs ) . ' /-->';
	}

	private function encode_request( string $serialized, string $hash = 'untrusted' ): WP_REST_Request {
		$request = new WP_REST_Request( 'POST', '/wp/v2/widget-types/tribe-widget-events-list/encode' );
		$request->set_param(
			'instance',
			[
				'encoded' => base64_encode( $serialized ),
				'hash'    => $hash,
			]
		);

		( new Service_Provider( tribe() ) )->enable_widget_copy_paste( null, rest_get_server(), $request );

		return $request;
	}

	private function decoded_instance( WP_REST_Request $request ): array {
		return unserialize( base64_decode( $request->get_param( 'instance' )['encoded'] ), [ 'allowed_classes' => false ] );
	}
}
