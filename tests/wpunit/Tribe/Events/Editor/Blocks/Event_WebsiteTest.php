<?php

namespace Tribe\Events\Editor\Blocks;

use Tribe__Events__Editor__Blocks__Event_Website;

class Event_WebsiteTest extends \Codeception\TestCase\WPTestCase {
	private $template;

	public function setUp(): void {
		// make sure to call parent setUp
		parent::setUp();

		// set up the request time
		$this->requestTimeSetUp();

		// Set up the template mock.
		$this->template = $this->getMockBuilder( '\Tribe__Events__Editor__Template' )
		                       ->setMethods( [ 'add_template_globals', 'template' ] )
		                       ->getMock();

		// Replace the instance in the container with the mock.
		tribe_singleton( 'events.editor.template', $this->template );
	}

	/**
	 * @return Tribe__Events__Editor__Blocks__Event_Website
	 */
	private function make_instance() {
		return new Tribe__Events__Editor__Blocks__Event_Website();
	}

	/**
	 * @test
	 */
	public function test_render_no_classes() {
		$this->template->method( 'template' )->willReturn( '' );

		$sut = $this->make_instance();

		$attributes = [];
		$result     = $sut->render( $attributes );

		$this->assertNotContains( 'class="', $result );
	}

	/**
	 * @test
	 */
	public function test_render_with_classes() {
		$this->template->method( 'template' )->willReturn( '' );

		$sut = $this->make_instance();

		$attributes = [ 'className' => 'test-class' ];
		$result     = $sut->render( $attributes );

		$this->assertContains( 'class="test-class"', $result );
	}
}
