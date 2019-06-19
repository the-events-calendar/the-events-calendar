<?php

namespace Tribe\Events\Views\V2;

class TemplateTest extends \Codeception\TestCase\WPTestCase {

	protected $slug = 'reflector';

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Template::class, $sut );
	}

	/**
	 * @return Template
	 */
	private function make_instance() {
		$view = View::make( $this->reflector );
		return new Template( $view );
	}

	public function prev_url_inputs() {
		return [
			'page_1' => [ 1, '' ],
			'page_2' => [ 2, 'http://example.com/?paged=1' ],
			'page_3' => [ 3, 'http://example.com/?paged=2' ],
		];
	}
}
