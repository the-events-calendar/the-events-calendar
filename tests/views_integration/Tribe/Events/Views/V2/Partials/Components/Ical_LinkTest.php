<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Ical_LinkTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/ical-link';

	public function test_render_with_context() {

		$ical = [
			'display_link' => true,
			'link'         => (object) [
				'url'    => 'http://tests.tri.be/?ical=something',
				'anchor' => 'Export Events',
				'title'  => 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'ical' => (object) $ical ] ) );
	}

	public function test_render_empty() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'ical' => (object) [ 'display_link' => false ] ] ) );
	}
}
