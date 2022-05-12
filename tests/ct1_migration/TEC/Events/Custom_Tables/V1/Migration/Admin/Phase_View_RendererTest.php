<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\Ajax;
use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Phase_View_RendererTest extends \CT1_Migration_Test_Case {

	use CT1_Fixtures;

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
		$site_report = Site_Report::build();
		$renderer    = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[
				'report'        => $site_report,
				'text'          => $text,
				'event_reports' => $site_report->get_event_reports( 1, 20 )
			]
		);
		$output      = $renderer->compile();

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
		$phase    = State::PHASE_MIGRATION_IN_PROGRESS;
		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php" );
		$text     = tribe( String_Dictionary::class );
		$renderer->register_node( 'progress-bar',
			'.tec-ct1-upgrade-update-bar-container',
			'/partials/progress-bar.php',
			[ 'report' => Site_Report::build(), 'phase' => $phase, 'text' => $text ]
		);

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
		$renderer    = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[
				'state'         => $state,
				'report'        => $site_report,
				'text'          => $text,
				'event_reports' => $site_report->get_event_reports( 1, 20 )
			]
		);

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

		$renderer = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[ 'state' => $state, 'report' => Site_Report::build(), 'text' => $text ]
		);

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

		$renderer = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[ 'state' => $state, 'report' => Site_Report::build(), 'text' => $text ]
		);

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
		$phase = State::PHASE_MIGRATION_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[ 'state' => $state, 'text' => $text ]
		);

		$output = $renderer->compile();

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
		$phase = State::PHASE_CANCEL_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[ 'state' => $state, 'text' => $text ]
		);

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
		$phase = State::PHASE_REVERT_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[ 'state' => $state, 'text' => $text ]
		);

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
		$phase       = State::PHASE_MIGRATION_COMPLETE;
		$state       = tribe( State::class );
		$text        = tribe( String_Dictionary::class );
		$site_report = Site_Report::build();
		$renderer    = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[
				'state'         => $state,
				'text'          => $text,
				'report'        => $site_report,
				'event_reports' => $site_report->get_event_reports( 1, 20 )
			]
		);

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
		$phase       = State::PHASE_CANCEL_COMPLETE;
		$state       = tribe( State::class );
		$site_report = Site_Report::build();
		$text        = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[
				'state'  => $state,
				'report' => $site_report,
				'text'   => tribe( String_Dictionary::class )
			]
		);

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
		$phase       = State::PHASE_REVERT_COMPLETE;
		$state       = tribe( State::class );
		$site_report = Site_Report::build();
		$text        = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[
				'state'  => $state,
				'report' => $site_report,
				'text'   => tribe( String_Dictionary::class )
			]
		);

		$output = $renderer->compile();

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
		$phase       = State::PHASE_MIGRATION_FAILURE_COMPLETE;
		$state       = tribe( State::class );
		$site_report = Site_Report::build();
		$text        = tribe( String_Dictionary::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/maintenance-mode/phase/$phase.php",
			[
				'state'  => $state,
				'report' => $site_report,
				'text'   => tribe( String_Dictionary::class )
			]
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-failed' ), $output['html'] );
	}

	/**
	 * Should render HTML from Maintenance Mode Migration Failure Complete templates.
	 *
	 * @test
	 */
	public function should_render_migration_failure_complete_ok() {
		// Setup templates.
		$phase         = State::PHASE_MIGRATION_FAILURE_COMPLETE;
		$state         = tribe( State::class );
		$site_report   = Site_Report::build();
		$text          = tribe( String_Dictionary::class );
		$event_reports = $site_report->get_event_reports( 1, 20 );

		$renderer = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[
				'state'         => $state,
				'report'        => $site_report,
				'event_reports' => $event_reports,
				'text'          => tribe( String_Dictionary::class )
			]
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( $text->get( 'migration-failure-complete' ), $output['html'] );
	}

	/**
	 * @test
	 */
	public function should_paginate_migration_prompt() {
		// Setup
		$this->given_number_single_event_reports( 150, true, 'faux-category', false );
		$phase       = State::PHASE_MIGRATION_PROMPT;
		$state       = tribe( State::class );
		$site_report = Site_Report::build();
		$ajax        = tribe( Ajax::class );

		$_GET['page']            = 1;
		$_GET['count']           = 20;
		$_GET['report_category'] = 'faux-category';
		$_GET['upcoming']        = true;
		$renderer                = $ajax->get_renderer_for_phase( $phase );

		// If we are paginating
		$events         = tribe( Events::class );
		$primary_filter = [
			Event_Report::META_KEY_MIGRATION_PHASE    => Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			'upcoming'                                => ! empty( $_GET['upcoming'] ),
			Event_Report::META_KEY_MIGRATION_CATEGORY => $_GET['report_category']
		];

		$event_reports = $site_report->get_event_reports( $_GET['page'], $_GET['count'], $primary_filter );
		$output        = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertNotEmpty( $event_reports );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$node = $output['nodes'][0];
		foreach ( $event_reports as $event_report ) {
			/**
			 * @var Event_Report $event_report
			 */
			$this->assertContains( $event_report->source_event_post->post_title, $node['html'] );
		}
	}
}