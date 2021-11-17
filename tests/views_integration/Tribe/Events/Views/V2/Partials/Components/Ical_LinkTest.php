<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe\Events\Views\V2\iCalendar\Links\iCal;
use Tribe\Events\Views\V2\iCalendar\Links\iCalendar_Export;

class Ical_LinkTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/ical-link';

	public function test_render_legacy_with_context() {
		add_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
		$ical = [
			'display_link' => true,
			'link'         => (object) [
				'url'   => 'http://tests.tri.be/?ical=something',
				'text'  => 'Export Events',
				'title' => 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps',
			],
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'ical' => (object) $ical ] ) );
		remove_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
	}

	public function test_render_legacy_empty() {
		add_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'ical' => (object) [ 'display_link' => false ] ] ) );
		remove_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
	}

	public function test_render_with_all() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subsc = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'subscribe_links' => $subsc ] ) );
	}

	public function test_render_with_gcal_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subsc = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$subsc['ical']->set_visibility( false );
		$subsc['ics']->set_visibility( false );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'subscribe_links' => $subsc ] ) );
	}

	public function test_render_with_ical_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subsc = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$subsc['gcal']->set_visibility( false );
		$subsc['ics']->set_visibility( false );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'subscribe_links' => $subsc ] ) );
	}

	public function test_render_with_icalendar_export_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subsc = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];
		$subsc['gcal']->set_visibility( false );
		$subsc['ical']->set_visibility( false );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'subscribe_links' => $subsc ] ) );
	}

	public function test_render_empty() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
