<?php

namespace Tribe\Events\Views\V2;

use org\bovigo\vfs\vfsStream;

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
	 * It should render the not found view if trying to render not registered view.
	 *
	 * @test
	 */
	public function should_render_the_not_found_view_if_trying_to_render_not_registered_view() {
		$view = View::make( 'not-set' );

		$template = $view->get_template();
		$this->assertInstanceOf( Template::class, $template );
		$this->assertEquals( $template->get_not_found_template(), $template->get_template_file() );

		$this->assertMatchesSnapshot( $view->get_html() );
	}

	/**
	 * It should render the base template if View has no template
	 *
	 * @test
	 */
	public function should_render_the_base_template_if_view_has_no_template() {
		add_filter( 'tribe_events_views', static function ( array $views ) {
			$views['test-base'] = Test_Full_View::class;

			return $views;
		} );
		$view = View::make( 'test-base' );

		$template = $view->get_template();
		$this->assertInstanceOf( Template::class, $template );
		$this->assertEquals( $template->get_base_template_file(), $template->get_template_file() );

		$this->assertMatchesSnapshot( $view->get_html() );
	}
}