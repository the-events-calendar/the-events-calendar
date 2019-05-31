<?php

namespace Tribe\Events;

use Tribe__Events__Main as TEC;
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
		// Let's make sure to set rewrite rules.
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = '/%postname%/';
		$wp_rewrite->rewrite_rules();

		// Create some categories we'll need.
		wp_create_tag( 'test' );
		wp_insert_term( 'test', TEC::TAXONOMY );
		list( $grandparent_id ) = array_values( wp_insert_term( 'grand-parent', TEC::TAXONOMY ) );
		list( $parent_id ) = array_values( wp_insert_term( 'parent', TEC::TAXONOMY, [ 'parent' => $grandparent_id ] ) );
		wp_insert_term( 'child', TEC::TAXONOMY, [ 'parent' => $parent_id ] );
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

	public function canonical_urls() {
		return [
			'not_ours'                => [
				'/?post_type=post&foo=bar',
				'/?post_type=post&foo=bar',
			],
			'list_page_1'             => [
				'/?post_type=tribe_events&eventDisplay=list',
				'/events/list/',
			],
			'list_page_2'             => [
				'/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/events/page/2/',
			],
			'list_page_1_w_extra'     => [
				'/?post_type=tribe_events&eventDisplay=list&foo=bar',
				'/events/list/?foo=bar',
			],
			'tag_page_1'              => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test',
				'/events/tag/test/list/',
			],
			'tag_page_1_w_extra'      => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test&foo=bar',
				'/events/tag/test/list/?foo=bar',
			],
			'tag_page_2'              => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test&paged=2',
				'/events/tag/test/page/2/',
			],
			'tag_page_2_w_extra'      => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test&paged=2&foo=bar',
				'/events/tag/test/page/2/?foo=bar',
			],
			'category_page_1'         => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test',
				'/events/category/test/list/',
			],
			'category_page_1_w_extra' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test&foo=bar',
				'/events/category/test/list/?foo=bar',
			],
			'category_page_2'         => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test&paged=2',
				'/events/category/test/page/2/',
			],
			'category_page_2_w_extra' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test&paged=2&foo=bar',
				'/events/category/test/page/2/?foo=bar',
			],
			'hierarchical_cats_page_1' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=child',
				'/events/category/grand-parent/parent/child/list/',
			],
			'hierarchical_cats_page_2' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=child&paged=2',
				'/events/category/grand-parent/parent/child/page/2/',
			],
			'hierarchical_cats_page_1_w_extra_args' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=child&foo=bar',
				'/events/category/grand-parent/parent/child/list/?foo=bar',
			],
			'hierarchical_cats_page_2_w_extra_args' => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=child&paged=2&foo=bar',
				'/events/category/grand-parent/parent/child/page/2/?foo=bar',
			],
			'day_page'                => [
				'/?post_type=tribe_events&eventDisplay=day&eventDate=2018-12-01',
				'/events/2018-12-01/',
			],
			'month_page'              => [
				'/?post_type=tribe_events&eventDisplay=month&eventDate=2018-12',
				'/events/month/2018-12/',
			],
			'feed_page'               => [
				'/?post_type=tribe_events&tag=test&feed=rss2',
				'/events/tag/test/feed/rss2/',
			],
			'ical_page'               => [
				'/?post_type=tribe_events&tag=test&ical=1',
				'/events/tag/test/ical/',
			],
		];
	}

	/**
	 * It should allow converting a URL to its canonical form
	 *
	 * @test
	 * @dataProvider canonical_urls
	 */
	public function should_allow_converting_a_url_to_its_canonical_form( $uri, $expected ) {
		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );
		$canonical_url = $rewrite->get_canonical_url( home_url( $uri ) );

		$this->assertEquals( home_url( $expected ), $canonical_url );
	}

	/**
	 * It should correctly parse not handled URLs
	 *
	 * @test
	 * @dataProvider not_handled_urls
	 */
	public function should_correctly_parse_not_handled_urls( $url ) {
		$this->assertEquals( $url, ( new Rewrite )->get_canonical_url( $url ) );
	}

	public function not_handled_urls() {
		return [
			'wo_trailing_slash'                 => [ 'http://example.com' ],
			'w_trailing_slash'                  => [ 'http://example.com/' ],
			'w_query_args'                      => [ 'http://example.com?foo=bar' ],
			'w_query_args_and_trailing_slash'   => [ 'http://example.com/?foo=bar' ],
			'w_url_fragment'                    => [ 'http://example.com#some-header' ],
			'w_url_fragment_and_trailing_slash' => [ 'http://example.com/#some-header' ],
			'w_everything'                      => [ 'http://example.com?foo=bar&some=foo#some-header' ],
			'w_everything_and_trailing_slash'   => [ 'http://example.com/?foo=bar&some=foo#some-header' ],
		];
	}

	/**
	 * It should let WP handle URLs we do not manage
	 *
	 * @test
	 */
	public function should_let_wp_handle_urls_we_do_not_manage() {
		$this->assertEquals( home_url(), ( new Rewrite() )->get_canonical_url( home_url() ) );
	}

	/**
	 * It should correctly handle a custom view URL
	 *
	 * @test
	 */
	public function should_correctly_handle_a_custom_view_url() {
		$url = home_url( '?view=some-view' );

		$canonical = ( new Rewrite() )->get_canonical_url( $url );

		$this->assertEquals( $url, $canonical );
	}

	/**
	 * It should correctly handle translated rules
	 *
	 * @test
	 * @dataProvider it_urls
	 */
	public function should_correctly_handle_translated_rules( $path, $expected_path ) {
		list( $it_rules, $it_bases ) = array_values( include( codecept_data_dir( 'rewrite/it-translated-rules.php' ) ) );
		$wp_rewrite        = new \WP_Rewrite();
		$wp_rewrite->rules = $it_rules;
		$rewrite           = new Rewrite( $wp_rewrite );
		$rewrite->bases    = $it_bases;

		$canonical = $rewrite->get_canonical_url( home_url( $path ) );

		$this->assertEquals( home_url( $expected_path ), $canonical );
	}

	public function it_urls() {
		return [
			'list_page_1' => [
				'/?post_type=tribe_events&eventDisplay=list',
				'/events/elenco/',
			],
			'list_page_2' => [
				'/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/events/pagina/2/',
			],
			'month'       => [
				'/?post_type=tribe_events&eventDisplay=month',
				'/events/mese/',
			],
			'featured'    => [
				'/?post_type=tribe_events&eventDisplay=list&featured=1',
				'/events/elenco/in-evidenza/',
			],
			'category'    => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test',
				'/events/categoria/test/elenco/',
			],
			'tag'    => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test',
				'/events/tag/test/elenco/',
			],
		];
	}

	/**
	 * It should allow parsing requests into query vars
	 *
	 * @test
	 * @dataProvider canonical_urls
	 */
	public function should_allow_parsing_requests_into_query_vars( $expected, $canonical_uri ) {
		$input_url   = home_url( $canonical_uri );
		parse_str( parse_url( $expected, PHP_URL_QUERY ), $expected_vars );

		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );
		$parsed_vars = $rewrite->parse_request( $input_url );

		$this->assertEquals( $expected_vars, $parsed_vars );
	}
}
