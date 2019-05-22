<?php

namespace Tribe\Events;

use Prophecy\Argument;
use Tribe__Events__Rewrite as Rewrite;

if ( ! class_exists( '\\SitePress' ) ) {
	require_once codecept_data_dir( 'classes/SitePress.php' );
}

class RewriteTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \WP_Rewrite
	 */
	protected $wp_rewrite;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->wp_rewrite = $this->prophesize( 'WP_Rewrite' );
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__Rewrite', $sut );
	}

	private function make_instance() {
		return new Rewrite( $this->wp_rewrite->reveal() );
	}

	/**
	 * @test
	 * it should filter post type link for supported post types only
	 */
	public function it_should_filter_post_type_link_for_supported_post_types_only() {
		$post = $this->factory()->post->create_and_get();

		$sut = $this->make_instance();

		$this->assertEquals( 'foo', $sut->filter_post_type_link( 'foo', $post ) );
	}
}
