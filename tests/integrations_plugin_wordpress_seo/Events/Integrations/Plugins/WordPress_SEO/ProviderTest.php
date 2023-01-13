<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;


/**
 * Class Provider_Test
 *
 * @since   5.0.8
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class ProviderTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @return Provider
	 */
	private function make_instance() {
		return new Provider( tribe() );
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Provider::class, $sut );
	}

	/**
	 * @test
	 */
	public function because_yoast_is_active_it_should_load() {
		$sut = $this->make_instance();

		$this->assertTrue( $sut->should_load() );
	}

	/**
	 * @test
	 */
	public function because_opengraph_is_set_to_true_load_events_schema_piece() {
		$sut = $this->make_instance();
		uopz_set_return( 'is_admin', false );

		\WPSEO_Options::set( 'opengraph', true );

		$pieces = apply_filters( 'wpseo_schema_graph_pieces', [], null );

		$events_schema = array_filter( $pieces, static function( $schema ) {
			return $schema instanceof Events_Schema;
		} );

		$this->assertNotEmpty( $events_schema );
	}

	/**
	 * @test
	 */
	public function because_opengraph_is_set_to_false_do_not_load_events_schema_piece() {
		$sut = $this->make_instance();
		uopz_set_return( 'is_admin', false );

		\WPSEO_Options::set( 'opengraph', false );

		$pieces = apply_filters( 'wpseo_schema_graph_pieces', [], null );

		$events_schema = array_filter( $pieces, static function( $schema ) {
			return $schema instanceof Events_Schema;
		} );

		$this->assertEmpty( $events_schema );
	}

	/**
	 * @test
	 */
	public function on_admin_do_not_load_events_schema_piece() {
		$sut = $this->make_instance();
		uopz_set_return( 'is_admin', true );

		\WPSEO_Options::set( 'opengraph', true );

		$pieces = apply_filters( 'wpseo_schema_graph_pieces', [], null );

		$events_schema = array_filter( $pieces, static function( $schema ) {
			return $schema instanceof Events_Schema;
		} );

		$this->assertEmpty( $events_schema );
	}
}
