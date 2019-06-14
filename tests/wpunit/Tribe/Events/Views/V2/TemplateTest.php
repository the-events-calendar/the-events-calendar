<?php

namespace Tribe\Events\Views\V2;

use Codeception\Exception\Fail;

class TemplateTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should allow setting the template slug
	 *
	 * @test
	 */
	public function should_allow_setting_the_template_slug() {
		$template = new Template( 'one' );

		$this->assertEquals( 'one', $template->get_slug() );

		$template->set_slug( 'two' );

		$this->assertEquals( 'two', $template->get_slug() );
	}

	/**
	 * It should check for template files one per request
	 *
	 * @test
	 */
	public function should_check_for_template_files_one_per_request() {
		$template = new Template( 'one' );
		add_filter( 'tribe_template_file', static function () {
			yield __FILE__;
			throw new Fail( 'This method should not be called twice.' );
		} );

		$template = new Template( 'test' );

		$template->get_template_file( 'test' );
		$template->get_template_file( 'test' );
		$template->get_template_file( 'test' );
	}
}
