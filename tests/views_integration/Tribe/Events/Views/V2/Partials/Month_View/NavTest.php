<?php

namespace Tribe\Events\Views\V2\Partials\Month_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class NavTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/nav';

	/**
	 * Test render with prev url with next url
	 */
	public function test_render_with_prev_url_with_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'  => '#',
			'prev_url'   => '#',
			'next_url'   => '#',
			'prev_label' => 'May',
			'next_label' => 'July',
			'location'   => 'calendar',
		] ) );
	}

	/**
	 * Test render with prev url without next url
	 */
	public function test_render_with_prev_url_without_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'  => '#',
			'prev_url'   => '#',
			'next_url'   => '',
			'prev_label' => 'May',
			'next_label' => 'July',
			'location'   => 'calendar',
		] ) );
	}

	/**
	 * Test render without prev url with next url
	 */
	public function test_render_without_prev_url_with_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'  => '#',
			'prev_url'   => '',
			'next_url'   => '#',
			'prev_label' => 'May',
			'next_label' => 'July',
			'location'   => 'calendar',
		] ) );
	}

	/**
	 * Test render without prev url without next url
	 */
	public function test_render_without_prev_url_without_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'  => '#',
			'prev_url'   => '',
			'next_url'   => '',
			'prev_label' => 'May',
			'next_label' => 'July',
			'location'   => 'calendar',
		] ) );
	}
}
