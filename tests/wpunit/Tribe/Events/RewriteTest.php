<?php

namespace Tribe\Events;

use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as Rewrite;
use function tad\WPBrowser\setPrivateProperties;

if ( ! class_exists( '\\SitePress' ) ) {
	require_once codecept_data_dir( 'classes/SitePress.php' );
}

class RewriteTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \WP_Rewrite
	 */
	protected $wp_rewrite;
	/**
	 * A map of Tribe__Events__Main::instance properties.
	 *
	 * @var array<string,string>
	 */
	protected $tec_prop_backup;

	/**
	 * A map of rewrite rules, in the format used by WordPress.
	 *
	 * @var array<string,string>
	 */
	protected $wp_rewrite_rules;

	public function setUp() {
		// before
		parent::setUp();

		tribe_unset_var( 'Tribe__Rewrite::get_handled_rewrite_rules' );
		tribe_unset_var( 'Tribe__Rewrite::get_localized_matchers' );
		tribe_unset_var( 'Tribe__Rewrite::get_rules_query_vars' );
		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// your set up methods here
		$this->wp_rewrite = $this->prophesize( 'WP_Rewrite' );
		// Let's make sure to set rewrite rules.
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = '/%postname%/';
		$wp_rewrite->rewrite_rules();

		// Backup the global $wp_rewrite object rules to avoid interference w/ other tests.
		$this->wp_rewrite_rules = $wp_rewrite->rules;

		// Create some categories we'll need.
		wp_create_tag( 'test' );
		wp_insert_term( 'test', TEC::TAXONOMY );
		list( $grandparent_id ) = array_values( wp_insert_term( 'grand-parent', TEC::TAXONOMY ) );
		list( $parent_id ) = array_values( wp_insert_term( 'parent', TEC::TAXONOMY, [ 'parent' => $grandparent_id ] ) );
		wp_insert_term( 'child', TEC::TAXONOMY, [ 'parent' => $parent_id ] );
		static::factory()->event = new Event();

		$tec_main              = TEC::instance();
		$this->tec_prop_backup = [
			'rewriteSlug' => $tec_main->rewriteSlug	,
			'rewriteSlugSingular' => $tec_main->rewriteSlugSingular,
		];
	}

	public function tearDown() {
		// Restore backed up properties on TEC main instance.
		if ( ! empty( $this->tec_prop_backup ) ) {
			$tec_main = TEC::instance();
			foreach ( $this->tec_prop_backup as $prop => $value ) {
				$tec_main->{$prop} = $value;
			}
		}

		// Restore the global $wp_rewrite rules array.
		global $wp_rewrite;
		$wp_rewrite->rules = $this->wp_rewrite_rules;

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
				'/events/list/page/2/',
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

	public function it_urls_data_provider() {
		return [
			'list_page_1' => [
				'/?post_type=tribe_events&eventDisplay=list',
				'/eventi/elenco/',
			],
			'list_page_2' => [
				'/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/eventi/elenco/pagina/2/',
			],
			'month'       => [
				'/?post_type=tribe_events&eventDisplay=month',
				'/eventi/mese/',
			],
			'featured'    => [
				'/?post_type=tribe_events&eventDisplay=list&featured=1',
				'/eventi/elenco/in-evidenza/',
			],
			'category'    => [
				'/?post_type=tribe_events&eventDisplay=list&tribe_events_cat=test',
				'/eventi/categoria/test/elenco/',
			],
			'tag'    => [
				'/?post_type=tribe_events&eventDisplay=list&tag=test',
				'/eventi/tag/test/elenco/',
			],
		];
	}

	/**
	 * It should correctly handle translated rules
	 *
	 * @test
	 * @dataProvider it_urls_data_provider
	 */
	public function should_correctly_handle_translated_rules( $path, $expected_path ): void {
		list( $it_rules, $it_bases ) = array_values( include( codecept_data_dir( 'rewrite/it-translated-rules.php' ) ) );
		update_option('rewrite_rules', $it_rules );
		$wp_rewrite        = new \WP_Rewrite();
		$wp_rewrite->rules = $it_rules;
		$rewrite           = new Rewrite( $wp_rewrite );
		$rewrite->bases    = $it_bases;

		$canonical = $rewrite->get_canonical_url( home_url( $path ) );

		$this->assertEquals( home_url( $expected_path ), $canonical );
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

	/**
	 * It should correctly passthru not handled vars when parsing requests
	 *
	 * @test
	 */
	public function should_correctly_passthru_not_handled_vars_when_parsing_requests() {
		$input_url = home_url( '/events/list/?not-handled=value' );
		$expected  = [
			'post_type' => 'tribe_events',
			'eventDisplay' => 'list',
			'not-handled'=>'value',
		];

		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );
		$parsed = $rewrite->parse_request( $input_url );

		$this->assertEqualSets( $expected, $parsed );
	}

	public function clean_url_data_set() {
		return [
			'already_clean'    => [ '/events/list', '/events/list/' ],
			'all_handled'      => [ '/events/list/?post_type=tribe_events', '/events/list/' ],
			'some_not_handled' => [ '/events/list/?post_type=tribe_events&foo=bar', '/events/list/?foo=bar' ],
		];
	}

	/**
	 * It should remove handled query vars from query string when cleaning URLs
	 *
	 * @test
	 * @dataProvider clean_url_data_set
	 */
	public function should_remove_handled_query_vars_from_query_string_when_cleaning_urls($input_uri, $expected) {
		$input_uri = home_url( $input_uri );
		$expected  = home_url( $expected );

		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );
		$clean_url = $rewrite->get_clean_url( $input_uri );

		$this->assertEquals( $expected, $clean_url );
	}

	public function list_rewrite_data_set() {
		return [
			'default_view_page_1'               => [ '/?post_type=tribe_events&eventDisplay=default', '/events/' ],
			'default_view_page_1_w_pagenum'     => [
				'/?post_type=tribe_events&eventDisplay=default&paged=1',
				'/events/'
			],
			'default_view_page_2'               => [
				'/?post_type=tribe_events&eventDisplay=default&paged=2',
				'/events/page/2/'
			],
			'list_view_page_1'                  => [ '/?post_type=tribe_events&eventDisplay=list', '/events/list/' ],
			'list_view_page_1_w_pagenum'        => [
				'/?post_type=tribe_events&eventDisplay=list&paged=1',
				'/events/list/'
			],
			'events_list_view_page_1'           => [
				'/events/?post_type=tribe_events&eventDisplay=list',
				'/events/list/'
			],
			'events_list_view_page_1_w_pagenum' => [
				'/events/?post_type=tribe_events&eventDisplay=list&paged=1',
				'/events/list/'
			],
			'list_view_page_2'                  => [
				'/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/events/list/page/2/'
			],
			'event_list_view_page_2'            => [
				'/events/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/events/list/page/2/'
			],
		];
	}

	/**
	 * It should correctly handle /list rewrites
	 *
	 * @test
	 * @dataProvider list_rewrite_data_set
	 */
	public function should_correctly_handle_list_rewrites( $input_uri, $expected_uri ) {
		$input_uri = home_url( $input_uri );
		$expected  = home_url( $expected_uri );

		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );
		$clean_url = $rewrite->get_clean_url( $input_uri );

		$this->assertEquals( $expected, $clean_url );
	}

	public function changed_archive_url_data_set() {
		return [
			'list_page_1' => [
				'/?post_type=tribe_events&eventDisplay=list',
				'/courses/list/',
			],
			'list_page_2' => [
				'/?post_type=tribe_events&eventDisplay=list&paged=2',
				'/courses/list/page/2/',
			],
			'month_view' => [
				'/?post_type=tribe_events&eventDisplay=month',
				'/courses/month/',
			],
			'day_page_1' => [
				'/?post_type=tribe_events&eventDisplay=day',
				'/courses/today/',
			],
			/*
			 * Where is past? The query var is removed and re-added before each transformation by Views that support it.
			 * We do not need to test it here.
			 */
		];
	}

	/**
	 * It should correctly build canonical URLs when /events archive slug changes
	 *
	 * @test
	 *
	 * @dataProvider changed_archive_url_data_set
	 */
	public function should_correctly_build_canonical_ur_ls_when_events_archive_slug_changes( $input_uri, $expected ) {
		$input_uri = home_url( $input_uri );
		$expected  = home_url( $expected );

		tribe_update_option( 'eventsSlug', 'courses' );

		$rewrite = new Rewrite;
		global $wp_rewrite;
		$rewrite->setup( $wp_rewrite );

		$this->assertEquals( $expected, $rewrite->get_clean_url( $input_uri ) );
	}

	/**
	 * It should allow single and archive events slugs to be the same
	 *
	 * @test
	 */
	public function should_allow_single_and_archive_events_slugs_to_be_the_same() {
		tribe_update_option( 'eventsSlug', 'course' );
		tribe_update_option( 'singleEventSlug', 'course' );
		$tec_main                      = TEC::instance();
		$tec_main->rewriteSlug         = 'course';
		$tec_main->rewriteSlugSingular = 'course';
		update_option(
			'rewrite_rules',
			json_decode( file_get_contents( codecept_data_dir( 'rewrite/course-rules.json' ) ), true )
		);
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = '/%postname%/';
		$expected_parsed = [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'list',
		];

		$rewrite = new Rewrite();
		$rewrite->setup( $wp_rewrite );
		$pretty_archive_url = home_url( '/course/list/' );
		$ugly_archive_url   = add_query_arg( $expected_parsed, home_url() );
		$parsed             = $rewrite->parse_request( $pretty_archive_url );
		$canonical_url = $rewrite->get_canonical_url( $ugly_archive_url );

		$this->assertEquals( $expected_parsed, $parsed );
		$this->assertEquals( $pretty_archive_url, $canonical_url );
	}

	public function lang_data_provider() {
		yield 'Spanish list view' => [
			'lang'          => 'es',
			'lang_code'     => 'en_ES',
			'args'          => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'list'
			],
			'expected_path' => '/eventos/lista/',
		];

		yield 'Spanish month view' => [
			'lang'          => 'es',
			'lang_code'     => 'en_ES',
			'args'          => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'month'
			],
			'expected_path' => '/eventos/mes/',
		];

		yield 'Japanese list view' => [
			'lang'          => 'ja',
			'lang_code'     => 'ja',
			'args'          => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'list'
			],
			'expected_path' => '/イベント/リスト/',
		];

		yield 'Japanese month view' => [
			'lang'          => 'ja',
			'lang_code'     => 'ja',
			'args'          => [
				'post_type'    => TEC::POSTTYPE,
				'eventDisplay' => 'month'
			],
			'expected_path' => '/イベント/月/',
		];
	}

	/**
	 * It should use the localized slug when available
	 *
	 * @test
	 * @dataProvider lang_data_provider
	 */
	public function should_use_the_localized_slug_when_available(
		string $lang,
		string $lang_code,
		array $args,
		string $expected_path
	) {
		list( $rewrite_rules, $bases, $localized_bases ) = array_values( include( codecept_data_dir( "rewrite/{$lang}-translated-rules.php" ) ) );
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		$wp_rewrite->rules               = $rewrite_rules;
		$wp_rewrite->permalink_structure = '/%postname%/';
		add_filter( 'locale', static function () use ( $lang_code ) {
			return $lang_code;
		} );
		add_filter( 'load_textdomain_mofile', static function ( $mofile, $domain ) use ( $lang_code ) {
			return codecept_data_dir( "lang/the-events-calendar-{$lang_code}.mo" );
		}, 99, 2 );


		wp_cache_flush();
		$rewrite = new Rewrite();
		$rewrite->setup( $wp_rewrite );
		$rewrite->bases = $bases;
		setPrivateProperties( $rewrite, [
			'localized_bases' => $localized_bases,
		] );
		$ugly_archive_url = add_query_arg( $args, home_url() );
		$canonical_url    = $rewrite->get_canonical_url( $ugly_archive_url );

		$this->assertEquals( home_url( $expected_path ), urldecode( $canonical_url ) );
	}

	/**
	 * It should correctly handle sub-directory installations when parsing requests
	 *
	 * @test
	 */
	public function should_correctly_handle_sub_directory_installations_when_parsing_requests() {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		// We're using permalinks.
		$wp_rewrite->permalink_structure = '/%postname%/';
		$expected_parsed                 = [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'default',
		];
		update_option('home', 'http://wordpress.test/test');

		$rewrite = new Rewrite();
		$rewrite->setup( $wp_rewrite );
		$parsed = $rewrite->parse_request( 'http://wordpress.test/test/events' );

		$this->assertEquals( $expected_parsed, $parsed );
	}

	/**
	 * It should correctly build canonical URL in subdirectory installation
	 *
	 * @test
	 */
	public function should_correctly_build_canonical_url_in_subdirectory_installation() {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		// We're using permalinks.
		$wp_rewrite->permalink_structure = '/%postname%/';
		update_option('home', 'http://wordpress.test/test');

		$rewrite = new Rewrite();
		$rewrite->setup( $wp_rewrite );
		$url = $rewrite->get_canonical_url( add_query_arg( [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'default',
		], 'http://wordpress.test/test' ) );

		$this->assertEquals( 'http://wordpress.test/test/events/', $url );
	}

	public function non_scalar_values_passthrough_data_provider() {
		yield 'one array query var' => [
			[
				'tribe_eventcategory' => [ 0 => 2 ],
			],
			'/events/?tribe_eventcategory[0]=2',
		];

		yield 'two array query vars w/ multiple values' => [
			[
				'tribe_eventcategory' => [ 0 => 2 ],
				'tribe_tags'          => [ 0 => 23, 1 => 89 ],
			],
			'/events/?tribe_eventcategory[0]=2&tribe_tags[0]=23&tribe_tags[1]=89',
		];
	}

	/**
	 * It should allow non-scalar query vars to pass through clean urls
	 *
	 * @test
	 * @dataProvider non_scalar_values_passthrough_data_provider
	 */
	public function should_allow_non_scalar_query_vars_to_pass_through_clean_urls(array $query_args, string $expected) {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		// We're using permalinks.
		$wp_rewrite->permalink_structure = '/%postname%/';
		$rewrite = new Rewrite();
		$rewrite->setup( $wp_rewrite );

		$clean_url = $rewrite->get_clean_url( add_query_arg( array_merge( [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'default',
		], $query_args ) ) );

		$this->assertEquals( $expected, str_replace( home_url(), '', urldecode( $clean_url ) ) );
	}

	/**
	 * It should correctly cache clean URLs with passthru vars
	 *
	 * @test
	 */
	public function should_correctly_cache_clean_urls_with_passthru_vars() {
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;
		// We're using permalinks.
		$wp_rewrite->permalink_structure = '/%postname%/';
		$rewrite                         = new Rewrite();
		$rewrite->setup( $wp_rewrite );
		$input_url = add_query_arg( [
			'post_type'    => TEC::POSTTYPE,
			'eventDisplay' => 'default',
			'tribe_tags'   => [ 0 => 23, 1 => 89 ],
		], home_url() );
		$expected  = '/events/?tribe_tags[0]=23&tribe_tags[1]=89';

		$clean_url_1 = $rewrite->get_clean_url( $input_url );
		$cached_clean_url_1 = $rewrite->get_clean_url( $input_url );
		$cached_clean_url_2 = $rewrite->get_clean_url( $input_url );
		$cached_canonical_url_1 = $rewrite->get_canonical_url( $input_url );
		$cached_canonical_url_2 = $rewrite->get_canonical_url( $input_url );

		$the_ = static function ( string $url ): string {
			return urldecode( str_replace( home_url(), '', $url ) );
		};
		$this->assertEquals( $expected, $the_($clean_url_1) );
		$this->assertEquals( $expected, $the_($cached_clean_url_1) );
		$this->assertEquals( $expected, $the_($cached_clean_url_2) );
		$this->assertEquals( $expected, $the_($cached_canonical_url_1) );
		$this->assertEquals( $expected, $the_($cached_canonical_url_2) );
	}
}
