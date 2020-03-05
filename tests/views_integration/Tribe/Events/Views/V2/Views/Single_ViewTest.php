<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Single_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// Remove v1 filtering to have consistent results.
		//remove_filter( 'tribe_events_before_html', [ TEC::instance(), 'before_html_data_wrapper' ] );
		//remove_filter( 'tribe_events_after_html', [ TEC::instance(), 'after_html_data_wrapper' ] );
	}

	/**
	 * @test
	 */
	public function test_should_single_event() {

		$event     = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'single-event/content', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertMatchesSnapshot( $html );
	}

}
