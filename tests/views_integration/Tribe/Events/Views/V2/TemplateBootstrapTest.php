<?php
namespace Tribe\Events\Views\V2;

use Tribe\Utils\Body_Classes;

class TemplateBootstrapTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Template_Bootstrap( new Manager() );
	}

	/**
	 * @test
	 */
	public function should_add_classes_to_queue() {
		$body_classes = tribe( Body_Classes::class );
		add_filter( 'tribe_body_class_should_add_to_queue', '__return_true' );
		$this->make_instance()->add_body_classes();

		$actual_classes = $body_classes->get_class_names();

		$this->assertContains( 'tribe-events-page-template', $actual_classes );
	}
}
