<?php

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;
use TEC\Common\StellarWP\Assets\Assets;
use Tribe\Events\Test\Traits\ECE_Maker;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class List_Page_Test extends Controller_Test_Case {
	use With_Uopz;
	use SnapshotAssertions;
	use ECE_Maker;
	use SnapshotAssertions;


	protected string $controller_class = List_Page::class;

	protected static array $backups = [];

	/**
	 * @before
	 */
	public function setup_admin_context() {
		global $current_screen, $submenu, $parent_file;
		self::$backups = [
			'current_screen' => $current_screen,
			'submenu'        => $submenu,
			'parent_file'    => $parent_file,
		];
		set_current_screen( 'edit-' . Calendar_Embeds::POSTTYPE );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * @after
	 */
	public function restore_backup() {
		global $current_screen, $submenu, $parent_file;
		$current_screen = self::$backups['current_screen'];
		$submenu        = self::$backups['submenu'];
		$parent_file    = self::$backups['parent_file'];
		set_current_screen( $current_screen );
		wp_set_current_user( 0 );
	}

	/**
	 * @test
	 */
	public function it_should_get_lists_url(): void {
		$controller = $this->make_controller();
		$this->assertEquals( home_url( 'wp-admin/edit.php?post_type=' . Calendar_Embeds::POSTTYPE . '&whenever=wherever' ), $controller->get_url( [ 'whenever' => rawurlencode( 'wherever' ) ] ) );
	}

	/**
	 * @test
	 * @dataProvider asset_data_provider
	 */
	public function it_should_locate_assets_where_expected( $slug, $path ) {
		$this->make_controller()->register();

		$this->assertTrue( Assets::init()->exists( $slug ) );

		// We use false, because in CI mode the assets are not build so min aren't available. Its enough to check that the non-min is as expected.
		$asset_url = Assets::init()->get( $slug )->get_url( false );
		$this->assertEquals( plugins_url( $path, TRIBE_EVENTS_FILE ), $asset_url );
	}

	public function asset_data_provider() {
		$assets = [
			'tec-events-calendar-embeds-script' => 'src/resources/js/calendar-embeds/admin/page.js',
			'tec-events-calendar-embeds-style'  => 'src/resources/css/calendar-embeds/admin/page.css',
		];

		foreach ( $assets as $slug => $path ) {
			yield $slug => [ $slug, $path ];
		}
	}

	/**
	 * @test
	 */
	public function it_should_register_menu_item() {
		global $submenu;

		$this->make_controller()->register();
		do_action( 'admin_menu' );

		// Check if submenu was created within main menu.
		$submenu_key = 'edit.php?post_type=' . TEC::POSTTYPE;
		$this->assertArrayHasKey( $submenu_key, $submenu );

		// Check submenu data.
		$submenu_data = $submenu[ $submenu_key ];
		$data = [ 'Embed Calendar', 'edit_published_tribe_events', 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE, 'Embed Calendar' ];
		$this->assertContains( $data, $submenu_data );
	}

	/**
	 * @test
	 */
	public function it_should_get_page_title() {
		$this->assertEquals('Embed Calendar', $this->make_controller()->get_page_title() );
	}

	/**
	 * @test
	 */
	public function it_should_overwrite_parent_file() {
		global $parent_file, $submenu_file;

		$this->make_controller()->register();

		// Test when parent file is not the Calendar Embeds post type.
		$parent_file = 'edit.php?post_type=some_other_post_type';
		$submenu_file = 'some_submenu_file';
		$result = apply_filters( 'submenu_file', $submenu_file );
		$this->assertEquals( 'some_submenu_file', $result );
		$this->assertEquals( 'edit.php?post_type=some_other_post_type', $parent_file );

		// Test when parent file is the Calendar Embeds post type.
		$parent_file = 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE;
		$submenu_file = 'some_submenu_file';
		$result = apply_filters( 'submenu_file', $submenu_file );
		$this->assertEquals( 'some_submenu_file', $result );
		$this->assertEquals( 'edit.php?post_type=' . TEC::POSTTYPE, $parent_file );
	}

	/**
	 * @test
	 */
	public function it_should_return_whether_we_are_on_page() {
		$this->assertTrue( List_Page::is_on_page() );

		$this->set_fn_return('get_current_screen', null );
		tribe( 'admin.pages' )->determine_current_page();
		$this->assertFalse( List_Page::is_on_page() );

		$this->set_fn_return('get_current_screen', (object) [ 'id' => 'edit-other_posttype' ] );
		tribe( 'admin.pages' )->determine_current_page();
		$this->assertFalse( List_Page::is_on_page() );
	}

	/**
	 * @test
	 */
	public function it_should_alter_columns() {
		$this->make_controller()->register();
		$new_columns = apply_filters( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns', [] );
		$this->assertEquals( '<input type="checkbox" />', $new_columns['cb'] );
		$this->assertEquals( 'Calendar Embeds', $new_columns['title'] );
		$this->assertEquals( 'Categories', $new_columns['event_categories'] );
		$this->assertEquals( 'Tags', $new_columns['event_tags'] );
		$this->assertEquals( 'Embed Snippet', $new_columns['snippet'] );
	}

	/**
	 * @test
	 */
	public function it_should_render_the_expected_column_content() {
		$columns = [ 'event_categories', 'event_tags', 'snippet' ];

		$content = [];

		$this->make_controller()->register();

		$ece_id = $this->create_ece();

		$tag_ids = $this->add_tags_to_ece( $ece_id, [ 'tag1', 'tag2' ] );

		$cat_ids = $this->add_categories_to_ece( $ece_id, [ 'cat1', 'cat2' ] );

		foreach ( $columns as $column ) {
			ob_start();
			apply_filters( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column', $column, $ece_id );
			$content[ $column ] = ob_get_clean();
		}

		$this->assertCount( 3, $content );

		$html = implode( PHP_EOL . '{COLUMN_DIVIDER}' . PHP_EOL, $content );
		$html = str_replace( (string) $ece_id, '{ECE_ID}', $html );
		$html = str_replace( $tag_ids, '{TAG_ID}', $html );
		$html = str_replace( $cat_ids, '{CAT_ID}', $html );

		$this->assertMatchesHtmlSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function it_should_render_the_expected_column_content_when_unpublished() {
		$columns = [ 'event_categories', 'event_tags', 'snippet' ];

		$content = [];

		$this->make_controller()->register();

		$ece_id = $this->create_ece( ['post_status' => 'draft' ] );

		$tag_ids = $this->add_tags_to_ece( $ece_id, [ 'tag1', 'tag2' ] );

		$cat_ids = $this->add_categories_to_ece( $ece_id, [ 'cat1', 'cat2' ] );

		foreach ( $columns as $column ) {
			ob_start();
			apply_filters( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column', $column, $ece_id );
			$content[ $column ] = ob_get_clean();
		}

		$this->assertCount( 3, $content );

		$html = implode( PHP_EOL . '{COLUMN_DIVIDER}' . PHP_EOL, $content );
		$html = str_replace( (string) $ece_id, '{ECE_ID}', $html );
		$html = str_replace( $tag_ids, '{TAG_ID}', $html );
		$html = str_replace( $cat_ids, '{CAT_ID}', $html );

		$this->assertMatchesHtmlSnapshot( $html );
	}
}
