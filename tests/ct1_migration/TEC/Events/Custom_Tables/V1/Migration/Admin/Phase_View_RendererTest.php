<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Custom_Tables\V1\Migration\Ajax;
use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Phase_View_RendererTest extends \CT1_Migration_Test_Case {
	use MatchesSnapshots;
	use CT1_Fixtures;

	public function setUp() {
		parent::setUp();
		global $wpdb;
		// clear our post state
		$keys = [ Event_Report::META_KEY_REPORT_DATA ];
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN('" . implode( "','", $keys ) . "')" );
		// clear our migration state
		update_option( State::STATE_OPTION_KEY, [] );
		tribe()->offsetUnset( Ajax::class );
		tribe()->offsetUnset( State::class );
	}

	/**
	 * Should find and structure the templates with their metadata.
	 *
	 * @test
	 */
	public function should_compile_view() {
		// Setup with some known templates.
		$phase    = State::PHASE_PREVIEW_IN_PROGRESS;
		$renderer = new Phase_View_Renderer( $phase, '/phase/preview-in-progress.php' );
		$renderer->register_node( 'progress-bar',
			'.tec-ct1-upgrade-update-bar-container',
			'/partials/progress-bar.php', [
				'report' => Site_Report::build(),
				'phase'  => $phase,
				'text'   => tribe( String_Dictionary::class )
			]
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertEquals( State::PHASE_PREVIEW_IN_PROGRESS, $output['key'] );
		$this->assertNotEmpty( $output['html'] );
		$this->assertIsString( $output['html'] );
		$this->assertIsArray( $output['nodes'] );
		foreach ( $output['nodes'] as $node ) {
			$this->assertNotEmpty( $node['html'] );
			$this->assertIsString( $node['html'] );
			$this->assertNotEmpty( $node['hash'] );
			$this->assertIsString( $node['hash'] );
			$this->assertNotEmpty( $node['key'] );
			$this->assertIsString( $node['key'] );
			$this->assertNotEmpty( $node['target'] );
			$this->assertIsString( $node['target'] );
		}
	}

	/**
	 * Should render HTML from Preview Prompt templates.
	 *
	 * @test
	 */
	public function should_render_preview_prompt_ok() {
		// Setup templates.
		$phase    = State::PHASE_PREVIEW_PROMPT;
		$text     = tribe( String_Dictionary::class );
		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php", [ 'text' => $text ] );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertEmpty( $output['nodes'] );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'start-migration-preview-button' ), $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade-start-migration-preview', $output['html'] );
	}

	/**
	 * Should render HTML from Preview In Progress templates.
	 *
	 * @test
	 */
	public function should_render_preview_in_progress_ok() {
		// Setup templates.
		$phase    = State::PHASE_PREVIEW_IN_PROGRESS;
		$text     = tribe( String_Dictionary::class );
		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php", [ 'text' => $text ] );
		$renderer->register_node( 'progress-bar',
			'.tec-ct1-upgrade-update-bar-container',
			'/partials/progress-bar.php',
			[ 'report' => Site_Report::build(), 'phase' => $phase, 'text' => $text ]
		);

		$output = $renderer->compile();
		$node   = array_pop( $output['nodes'] );

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . State::PHASE_PREVIEW_IN_PROGRESS, $output['html'] );
		$this->assertContains( $text->get( 'preview-in-progress' ), $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade-update-bar-container', $output['html'] );
		$this->assertContains( 'tribe-update-bar__summary-progress-text', $node['html'] );

	}

	/**
	 * Should render migration page properly (with migration button etc) when in a convoluted state.
	 *
	 * @test
	 */
	public function should_render_migration_prompt_with_error_no_preview_ok() {
		// Setup some faux state
		$faux_post1 = tribe_events()->set_args( [
			'title'      => "Event " . rand( 1, 999 ),
			'start_date' => date( 'Y-m-d H:i:s' ),
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		$event_report = ( new Event_Report( $faux_post1 ) )
			->start_event_migration()
			->set_tickets_provider( 'woocommerce' )
			->set( 'is_single', true )
			->add_strategy( 'split' );
		$event_report->migration_failed( 'Failure' );

		// Setup templates.
		$phase = State::PHASE_MIGRATION_PROMPT;
		$time  = time();
		$text  = tribe( String_Dictionary::class );
		$state = tribe( State::class );
		$state->set( 'preview_unsupported', true );
		$state->set( 'complete_timestamp', $time );
		$state->save();
		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );
		$output   = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertEmpty( $output['nodes'] );
		$this->assertMatchesSnapshot( $output['html'] );
	}

	/**
	 * Should render HTML from Migration Prompt templates.
	 *
	 * @test
	 */
	public function should_render_migration_prompt_ok() {
		// Setup templates.
		$phase = State::PHASE_MIGRATION_PROMPT;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );
		$time  = time();
		$state->set( 'complete_timestamp', $time );
		$state->save();
		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );
		$output   = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertEmpty( $output['nodes'] );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__alert', $output['html'] );
		$this->assertContains( $text->get( 'start-migration-button' ), $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__report-body-content', $output['html'] );
		$this->assertContains( wp_date( 'F j, Y, g:i a', $time ), $output['html'] );
	}

	/**
	 * Should render HTML from Migration In Progress templates.
	 *
	 * @test
	 */
	public function should_render_migration_in_progress_ok() {
		// Setup templates.
		$phase = State::PHASE_MIGRATION_IN_PROGRESS;

		$text     = tribe( String_Dictionary::class );
		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );


		$output = $renderer->compile();
		$node   = array_pop( $output['nodes'] );

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade-update-bar-container', $output['html'] );
		$this->assertContains( 'tribe-update-bar__summary-progress-text', $node['html'] );
		$this->assertContains( $text->get( 'migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Migration Complete templates.
	 *
	 * @test
	 */
	public function should_render_migration_complete_ok() {
		// Setup templates.
		$phase       = State::PHASE_MIGRATION_COMPLETE;
		$state       = tribe( State::class );
		$text        = tribe( String_Dictionary::class );
		$site_report = Site_Report::build();
		$ajax        = tribe( Ajax::class );
		$renderer    = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__link-danger', $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__report-body-content', $output['html'] );
		$this->assertContains( $text->get( 'migration-complete' ), $output['html'] );
	}

	/**
	 * Should render HTML from Cancel In Progress templates.
	 *
	 * @test
	 */
	public function should_render_cancel_in_progress_ok() {
		// Setup templates.
		$phase = State::PHASE_CANCEL_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'cancel-migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Revert In Progress templates.
	 *
	 * @test
	 */
	public function should_render_revert_in_progress_ok() {
		// Setup templates.
		$phase = State::PHASE_REVERT_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'reverse-migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Migration In Progress templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_migration_in_progress_ok() {
		// Setup templates.
		$phase                       = State::PHASE_MIGRATION_IN_PROGRESS;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );
		$output   = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Cancel In Progress templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_cancel_in_progress_ok() {
		// Setup templates.
		$phase                       = State::PHASE_CANCEL_IN_PROGRESS;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'cancel-migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Revert In Progress templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_revert_in_progress_ok() {
		// Setup templates.
		$phase                       = State::PHASE_REVERT_IN_PROGRESS;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );
		$ajax                        = tribe( Ajax::class );
		$renderer                    = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'reverse-migration-in-progress' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Migration Complete templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_migration_complete_ok() {
		// Setup templates.
		$phase                       = State::PHASE_MIGRATION_COMPLETE;
		$text                        = tribe( String_Dictionary::class );
		$_GET["is_maintenance_mode"] = '1';
		$ajax                        = tribe( Ajax::class );
		$renderer                    = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-complete' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Migration Canceled templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_migration_cancel_complete_ok() {
		// Setup templates.
		$phase                       = State::PHASE_CANCEL_COMPLETE;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );
		$ajax                        = tribe( Ajax::class );
		$renderer                    = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-canceled' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Migration Reverse Complete templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_migration_reverse_complete_ok() {
		// Setup templates.
		$phase                       = State::PHASE_REVERT_COMPLETE;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );
		$output   = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-reversed' ), $output['html'] );
	}


	/**
	 * Should render HTML from Maintenance Mode Migration Failure Complete templates.
	 *
	 * @test
	 */
	public function should_render_maintenance_migration_failure_complete_ok() {
		// Setup templates.
		$phase                       = State::PHASE_MIGRATION_FAILURE_COMPLETE;
		$_GET["is_maintenance_mode"] = '1';
		$text                        = tribe( String_Dictionary::class );

		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-failed' ), $output['html'] );
	}

	/**
	 * Should render HTML from  Migration Failure Complete templates.
	 *
	 * @test
	 */
	public function should_render_migration_failure_complete_ok() {
		// Setup templates.
		$phase    = State::PHASE_MIGRATION_FAILURE_COMPLETE;
		$text     = tribe( String_Dictionary::class );
		$ajax     = tribe( Ajax::class );
		$renderer = $ajax->get_renderer_for_phase( $phase );
		$output   = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-failure-complete' ), $output['html'] );
	}


	/**
	 * @return array
	 */
	public function paginate_provider() {
		return array(
			'Page 1, Count 50, Total Events 150, Upcoming Events' => array( 1, 50, 150, true ),
			'Page 1, Count 50, Total Events 150, Past Events'     => array( 1, 50, 150, false ),
			'Page 1, Count 50, Total Events 10, Upcoming Events'  => array( 1, 50, 10, true ),
			'Page 1, Count 50, Total Events 10, Past Events'      => array( 1, 50, 10, false ),
			'Page 3, Count 10, Total Events 22, Upcoming Events'  => array( 3, 10, 22, true ),
			'Page 4, Count 10, Total Events 22, Upcoming Events'  => array( 4, 10, 22, true ),

		);
	}

	/**
	 * Should generate the HTML nodes for the pagination queries.
	 * @dataProvider paginate_provider
	 * @test
	 *
	 * @param int     $page
	 * @param int     $count
	 * @param int     $total
	 * @param boolean $upcoming
	 */
	public function should_paginate_migration_prompt( $page, $count, $total, $upcoming ) {
		// Setup
		$category = 'faux-category';
		$this->given_number_single_event_reports( $total, $upcoming, $category, false );
		$phase = State::PHASE_MIGRATION_PROMPT;


		$state = tribe( State::class );
		$state->set( 'phase', $phase );
		$state->save();
		$site_report = Site_Report::build();
		$ajax        = tribe( Ajax::class );

		$primary_filter = [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => $upcoming,
			Event_Report::META_KEY_MIGRATION_CATEGORY => $category
		];

		$event_reports = $site_report->get_event_reports( $page, $count, $primary_filter );

		// If we are paginating
		$output = $ajax->get_paginated_response( $page, $count, $upcoming, $category );

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		// Should have events?
		$start       = $page === 1 ? 0 : ( $page - 1 ) * $count;
		$should_have = $start < $total;
		if ( $should_have ) {
			$this->assertContains( 'tec-ct1-upgrade-event-item', $output['html'] );
		} else {
			$this->assertNotContains( 'tec-ct1-upgrade-event-item', $output['html'] );
		}
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );

		foreach ( $event_reports as $event_report ) {
			/**
			 * @var Event_Report $event_report
			 */
			$this->assertContains( $event_report->source_event_post->post_title, $output['html'] );
		}
	}
}