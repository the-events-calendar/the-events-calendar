<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class MessagesTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/messages';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {

		$messages = [
			'notice' => [
				'There were no results found',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'messages' => $messages ] ) );
	}

	/**
	 * Test render with multiple messages.
	 */
	public function test_render_with_multiple_messages_html() {

		$messages = [
			'notice' => [
				'There were no results found',
			],
			'error' => [
				'The first rule of fight club is, you do not talk about fight club.',
				'The second rule of fight club is, <strong>you do not talk about fight club.</strong>',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'messages' => $messages ] ) );
	}

	/**
	 * Test render empty
	 */
	public function test_render_no_messages() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [] ) );
	}

	/**
	 * Test render with HTML
	 */
	public function test_render_with_html() {

		$messages = [
			'notice' => [
				'There were no results found for <strong>this amazing search</strong>',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'messages' => $messages ] ) );
	}
}
