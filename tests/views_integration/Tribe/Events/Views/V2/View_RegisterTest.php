<?php

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Test\Factories\Event;

// Include a Test View Class
require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );
require_once codecept_data_dir( 'Views/V2/classes/Publicly_Visible_Test_View.php' );

class View_RegisterTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * A backup of the global `$wp` object query vars.
	 *
	 * @var string[]|RewriteTest
	 */
	protected static $wp_public_query_vars;

	/**
	 * The original, global WP_Rewrite.
	 *
	 * @var \WP_Rewrite
	 */
	protected static $wp_rewrite;

	/**
	 * @var \Tribe\Events\Pro\Rewrite\Rewrite
	 */
	protected $rewrite;

	public static $original_permalink_structure;

	public function setUp() {
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		static::factory()->event = new Event();
	}

	public static function wpSetUpBeforeClass() {
		static::factory()->event = new Event();
		// Let's backup the query vars to make sure we're using the full ones.
		global $wp, $wp_rewrite;
		$wp_rewrite->permalink_structure = '/%postname%/';
		$wp_rewrite->rewrite_rules();
		static::$wp_public_query_vars = $wp->public_query_vars;
		static::$wp_rewrite = $wp_rewrite;

		// Let's create some terms that we'll be using.
		wp_insert_term( 'bacon', 'post_tag' );
		wp_insert_term( 'potato', 'tribe_events_cat' );

		static::$original_permalink_structure = get_option( 'permalink_structure' );
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}

	public static function tearDownAfterClass() {
		global $wp, $wp_rewrite;
		$wp->public_query_vars = static::$wp_public_query_vars;
		$wp_rewrite            = static::$wp_rewrite;
		update_option( 'permalink_structure', static::$original_permalink_structure );
		flush_rewrite_rules();
		parent::tearDownAfterClass();
	}

	/**
	 * @return Manager
	 */
	private function make_instance( $slug = 'test', $name = 'Test View', $class = List_View::class, $priority = 50 ) {
		return tribe_register_view( $slug, $name, $class, $priority );
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( View_Register::class, $sut );
	}

	/**
	 * @test
	 * @dataProvider endpoint_provider
	 */
	public function it_should_contain_the_registered_view_in_rewrite_routes( $url, $args ) {
		global $wp, $wp_rewrite;

		$sut = $this->make_instance( 'bork' );

		wp_cache_flush();
		$wp_rewrite->permalink_structure = '/%postname%/';
		// Let's make sure to set rewrite rules.
		$wp->public_query_vars = static::$wp_public_query_vars;

		$rewrite        = \Tribe__Events__Rewrite::instance();
		$rewrite->bases = null;
		$rewrite->rules = null;
		$rewrite->setup( $wp_rewrite );

		$original_rewrite = tribe( 'events.rewrite' );
		tribe_unset_var( 'Tribe__Rewrite::get_handled_rewrite_rules' );
		tribe_register( 'events.rewrite', $rewrite );

		flush_rewrite_rules();

		$pretty_archive_url = home_url( $url );
		$ugly_archive_url   = add_query_arg( $args, home_url() );

		$parsed             = $rewrite->parse_request( $pretty_archive_url );
		$canonical_url      = $rewrite->get_canonical_url( $ugly_archive_url );

		$this->assertEquals( $args, $parsed );
		$this->assertEquals( $pretty_archive_url, $canonical_url );

		tribe_register( 'events.rewrite', $original_rewrite );
		tribe_unset_var( 'Tribe__Rewrite::get_handled_rewrite_rules' );
	}

	public function endpoint_provider() {
		yield 'base view' => [
			'url'  => '/events/bork/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
			],
		];

		yield 'paged' => [
			'url'  => '/events/bork/page/2/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'paged'        => 2,
			],
		];

		yield 'featured' => [
			'url'  => '/events/bork/featured/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'featured'     => true,
			],
		];

		yield 'featured and paged' => [
			'url'  => '/events/bork/featured/page/3/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'featured'     => true,
				'paged'        => 3,
			],
		];

		yield 'base view with date' => [
			'url'  => '/events/bork/2020-01-30/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'eventDate'    => '2020-01-30',
			],
		];

		yield 'featured with date' => [
			'url'  => '/events/bork/2020-01-30/featured/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'featured'     => true,
				'eventDate'    => '2020-01-30',
			],
		];

		yield 'tag' => [
			'url'  => '/events/tag/bacon/bork/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'tag'          => 'bacon',
			],
		];

		yield 'tag featured' => [
			'url'  => '/events/tag/bacon/bork/featured/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'tag'          => 'bacon',
				'featured'     => true,
			],
		];

		yield 'tag featured and paged' => [
			'url'  => '/events/tag/bacon/bork/featured/page/2/',
			'args' => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'bork',
				'tag'          => 'bacon',
				'featured'     => true,
				'paged'        => 2,
			],
		];

		yield 'category' => [
			'url'  => '/events/category/potato/bork/',
			'args' => [
				'post_type'        => TEC::POSTTYPE,
				'eventDisplay'     => 'bork',
				'tribe_events_cat' => 'potato',
			],
		];

		yield 'category featured' => [
			'url'  => '/events/category/potato/bork/featured/',
			'args' => [
				'post_type'        => TEC::POSTTYPE,
				'eventDisplay'     => 'bork',
				'tribe_events_cat' => 'potato',
				'featured'         => true,
			],
		];

		yield 'category featured and paged' => [
			'url'  => '/events/category/potato/bork/featured/page/3/',
			'args' => [
				'post_type'        => TEC::POSTTYPE,
				'eventDisplay'     => 'bork',
				'tribe_events_cat' => 'potato',
				'featured'         => true,
				'paged'            => 3,
			],
		];
	}
}
