<?php

namespace Tribe\Events\Views\V2;

use org\bovigo\vfs\vfsStream;
use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\Products\WPBrowser\Views\V2\TestCase;

require_once codecept_data_dir( 'Views/V2/classes/Test_Template_View.php' );
require_once codecept_data_dir( 'Views/V2/classes/Test_Full_View.php' );

class ExtendingViewTest extends TestCase {

	protected $unlink_on_tear_down = [];

	public function setUp() {
		parent::setUp();
		add_filter( 'tribe_events_views', static function ( array $views ) {
			$views['test'] = Test_Template_View::class;

			return $views;
		} );

		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * It should use the base template if no template is found
	 *
	 * @test
	 */
	public function should_use_the_base_template_if_no_template_is_found() {
		$view = View::make( Test_Template_View::class );

		$view_template = $view->get_template();
		$view_template_file = $view_template->get_template_file();

		$this->assertNotEmpty( $view_template_file );
		$this->assertEquals( $view_template->get_base_template_file(), $view_template_file );
	}

	/**
	 * It should look for the view template in the default path
	 *
	 * @test
	 */
	public function should_look_for_the_view_template_in_the_default_path() {
		$view = View::make( Test_Template_View::class );
		$view_template = $view->get_template();
		$template_dir = dirname( $view_template->get_base_template_file() );
		$template_path = $template_dir . '/test.php';
		file_put_contents( $template_path, '<p>Test view template</p>' );
		$this->unlink_on_tear_down[] = $template_path;
		$view_template_file = $view_template->get_template_file();

		$this->assertNotEmpty( $view_template_file );
		$this->assertEquals( $template_path, $view_template_file );
	}

	/**
	 * It should allow filtering the template folders
	 *
	 * @test
	 */
	public function should_allow_filtering_the_template_folders() {
		$this->markTestSkipped( 'Due to changes in common' );
		$template_folder = vfsStream::setup( 'templates', 0777, [ 'test.php' => '<p>Hay there!</p>' ] );
		add_filter( 'tribe_template_path_list', static function ( array $folders ) use ( $template_folder ) {
			$folders[] = [
				'id'       => 'test-test',
				'priority' => 1,
				'path'     => $template_folder->url(),
			];

			return $folders;
		} );

		$view = View::make( Test_Template_View::class );
		$view_template = $view->get_template();
		$view_template_file = $view_template->get_template_file();

		$this->assertNotEmpty( $view_template_file );
		$this->assertEquals( $template_folder->url() . '/test.php', $view_template_file );
	}

	public function tearDown() {
		foreach ( $this->unlink_on_tear_down as $file ) {
			if ( file_exists( $file ) ) {
				$this->unlink( $file );
			}
		}
		parent::tearDown();
	}

	/**
	 * It should fetch the template from the theme if available
	 *
	 * @test
	 */
	public function should_fetch_the_template_from_the_theme_if_available() {
		$structure = [ 'tribe' => [ 'views' => [ 'v2' => [ 'test.php' => '<p>Hey there!</p>' ] ] ] ];
		$theme = vfsStream::setup( 'custom-theme', 0777, $structure );
		add_filter( 'tribe_template_public_path', static function () use ( $theme ) {
			return $theme->url() . '/tribe/views/v2';
		} );

		$view = View::make( Test_Template_View::class );
		$view_template = $view->get_template();
		$view_template_file = $view_template->get_template_file();

		$this->assertNotEmpty( $view_template_file );
		$this->assertEquals( $theme->url() . '/tribe/views/v2/test.php', $view_template_file );
	}

	/**
	 * @test
	 */
	public function should_render_the_default_view_if_view_not_found() {
		add_filter( 'tribe_events_views', static function () {
			return [
				'list'      => List_View::class,
				'month'     => Month_View::class,
			];
		} );
		$view = View::make( 'not-set', tribe_context()->alter( [
			'today' => '2020-01-01',
			'now'   => '2020-01-01 09:00:00'
		] ) );

		$this->assertInstanceOf( List_View::class, $view );
		$template = $view->get_template();
		$this->assertInstanceOf( Template::class, $template );
		$this->assertNotEquals( $template->get_not_found_template(), $template->get_template_file() );

		$this->assertMatchesSnapshot( $view->get_html() );
	}

	/**
	 * It should render the base template if View has no template
	 *
	 * @test
	 */
	public function should_render_the_base_template_if_view_has_no_template() {
		add_filter( 'tribe_events_views', static function ( array $views ) {
			$views['test-full'] = Test_Full_View::class;

			return $views;
		} );
		$view = View::make( 'test-full' );

		$template = $view->get_template();
		$this->assertInstanceOf( Template::class, $template );
		$this->assertEquals( $template->get_base_template_file(), $template->get_template_file() );

		$this->assertMatchesSnapshot( $view->get_html() );
	}
}
