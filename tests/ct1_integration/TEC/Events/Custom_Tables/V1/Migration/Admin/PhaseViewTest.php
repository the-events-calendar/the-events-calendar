<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events_Pro\Custom_Tables\V1\Event_Factory;
use WP_Post;

class PhaseViewTest extends \Codeception\TestCase\WPTestCase {

	/**

	 *
	 * @test
	 */
	public function should_compile_view() {
		$renderer = new Phase_View_Renderer(State::PHASE_PREVIEW_IN_PROGRESS, TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration/phase/preview-in-progress.php');
		$renderer->register_node( 'progress-bar',
			'.tribe-update-bar-container',
			TEC_CUSTOM_TABLES_V1_ROOT . '/admin-views/migration/partials/progress-bar.php'
		);

$output = $renderer->compile();
		$this->assertNotEmpty($output);
		$this->assertEquals(State::PHASE_PREVIEW_IN_PROGRESS,$output['key']);
		$this->assertNotEmpty($output['html']);
	}

}