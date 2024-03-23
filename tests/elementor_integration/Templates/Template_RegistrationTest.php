<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;


class Template_RegistrationTest extends WPTestCase {
	use With_Uopz;

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Test free template registration.
	 * Ensures the Event_Single is registered under 'tec_event_single'.
	 */
	public function test_free_template_registration() {
		$type = \Elementor\Plugin::$instance->documents->get_document_type( Documents\Event_Single::get_type() );

		$this->assertEquals( $type, Documents\Event_Single::class );
	}
}
