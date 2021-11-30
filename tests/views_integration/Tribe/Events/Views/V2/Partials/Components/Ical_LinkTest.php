<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe\Events\Views\V2\iCalendar\Links\iCal;
use Tribe\Events\Views\V2\iCalendar\Links\iCalendar_Export;

class Ical_LinkTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/ical-link';

	/**
	 * Trims out the date-based parts for snapshots.
	 *
	 * @since 5.12.0
	 *
	 * @param string $html
	 * @return string $html
	 */
	public function trim_snapshot( $html ) {
		return preg_replace('/tribe-bar-date\S*(ical)/', 'ical', $html );
	}

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

		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'ical' => (object) $ical ] )
			)
		);

		remove_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
	}

	public function test_render_legacy_empty() {
		add_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'ical' => (object) [ 'display_link' => false ] ] )
				)
		);
		remove_filter( 'tec_views_v2_use_subscribe_links', '__return_false' );
	}

	public function test_render_empty() {
		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html()
				)
		);
	}

	public function test_render_with_all() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subs = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'subscribe_links' => $subs ] )
				)
		);
	}

	public function test_render_with_gcal_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subs = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$subs['ical']->set_visibility( false );
		$subs['ics']->set_visibility( false );

		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'subscribe_links' => $subs ] )
				)
		);
	}

	public function test_render_with_ical_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subs = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];

		$subs['gcal']->set_visibility( false );
		$subs['ics']->set_visibility( false );

		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'subscribe_links' => $subs ] )
				)
		);
	}

	public function test_render_with_icalendar_export_only() {
		$gcal = new Google_Calendar;
		$ical = new iCal;
		$ics  = new iCalendar_Export;

		$subs = [
			'gcal' => $gcal,
			'ical' => $ical,
			'ics'  => $ics,
		];
		$subs['gcal']->set_visibility( false );
		$subs['ical']->set_visibility( false );

		$this->assertMatchesSnapshot(
			$this->trim_snapshot(
				$this->get_partial_html( [ 'subscribe_links' => $subs ] )
				)
		);
	}
}
