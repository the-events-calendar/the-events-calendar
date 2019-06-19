<?php
/**
 * The base test case to test v2 Views HTML Partials.
 *
 * It provides utility methods and assertions useful and required in Views testing.
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use DOMWrap\Document;

/**
 * Class TestCase
 *
 * @package Tribe\Events\Views\V2
 */
abstract class TestHtmlCase extends TestCase {
	/**
	 * Store the views loader
	 * @var Tribe\Events\Views\V2\Template
	 */
	protected $template;

	/**
	 * Store the DOM handler
	 * @var DOMWrap\Document
	 */
	protected $document;

	public function setUp() {
		parent::setUp();

		$this->view     = $this->make_view_instance();
		$this->template = $this->make_template_instance();
		$this->document = $this->make_document_instance();
	}

	protected function make_view_instance() {
		return View::make( 'reflection' );
	}

	protected function make_template_instance() {
		return new Template( $this->view );
	}

	protected function make_document_instance() {
		return new Document();
	}
}