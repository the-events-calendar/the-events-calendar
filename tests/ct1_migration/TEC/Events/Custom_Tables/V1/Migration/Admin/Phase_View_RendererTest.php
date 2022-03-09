<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strings;

class Phase_View_RendererTest extends \CT1_Migration_Test_Case {

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
				'text'   => tribe( Strings::class )
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
		$text     = tribe( Strings::class );
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
		$text     = tribe( Strings::class );
		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php", [ 'text' => $text ] );
		$renderer->register_node( 'progress-bar',
			'.tec-ct1-upgrade-update-bar-container',
			'/partials/progress-bar.php',
			[ 'report' => Site_Report::build(), 'phase' => $phase ,  'text' => $text ]
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
		$text     = tribe( Strings::class );
		$time  = time();
		$state->set( 'complete_timestamp', $time );
		$state->save();

		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php",
			[ 'report' => Site_Report::build( 1, 20 ), 'text' => $text ]
		);
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
		$phase    = State::PHASE_MIGRATION_IN_PROGRESS;
		$renderer = new Phase_View_Renderer( $phase, "/phase/$phase.php" );
		$renderer->register_node( 'progress-bar',
			'.tec-ct1-upgrade-update-bar-container',
			'/partials/progress-bar.php',
			[ 'report' => Site_Report::build(), 'phase' => $phase, 'text' => tribe( Strings::class ) ]
		);

		$output = $renderer->compile();
		$node   = array_pop( $output['nodes'] );

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade-update-bar-container', $output['html'] );
		$this->assertContains( 'tribe-update-bar__summary-progress-text', $node['html'] );
	}

	/**
	 * Should render HTML from Migration Complete templates.
	 *
	 * @test
	 */
	public function should_render_migration_complete_ok() {
		// Setup templates.
		$phase    = State::PHASE_MIGRATION_COMPLETE;
		$state    = tribe( State::class );
		$renderer = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[ 'state' => $state, 'report' => Site_Report::build( 1, 20 ), 'text' => tribe( Strings::class ) ]
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__link-danger', $output['html'] );
		$this->assertContains( 'tec-ct1-upgrade__report-body-content', $output['html'] );
	}

	/**
	 * Should render HTML from Undo In Progress templates.
	 *
	 * @test
	 */
	public function should_render_undo_in_progress_ok() {
		// Setup templates.
		$phase = State::PHASE_UNDO_IN_PROGRESS;
		$state = tribe( State::class );
		$text  = tribe( Strings::class );

		$renderer = new Phase_View_Renderer( $phase,
			"/phase/$phase.php",
			[ 'state' => $state, 'report' => Site_Report::build( 1, 20 ), 'text' => $text ]
		);

		$output = $renderer->compile();

		// Check for expected compiled values.
		$this->assertNotEmpty( $output );
		$this->assertContains( 'tec-ct1-upgrade--' . $phase, $output['html'] );
	}
}