<?php

namespace Tribe\Events\Aggregator;

use Tribe\Events\Admin\Settings as Plugin_Settings;
use Tribe__Settings_Tab as Settings_Tab;

class Import_Settings_TabTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * The screen in place before each test.
	 *
	 * @var WP_Screen|null
	 */
	private $previous_screen;

	/**
	 * @before
	 */
	public function set_settings_screen(): void {
		// Other tests leave a stub screen without an `id`, which the settings page lookup reads.
		$this->previous_screen = $GLOBALS['current_screen'] ?? null;
		set_current_screen( 'tribe_events_page_tec-events-settings' );
	}

	/**
	 * @after
	 */
	public function restore_screen(): void {
		$GLOBALS['current_screen'] = $this->previous_screen;
	}

	/**
	 * @test
	 * It should point the Meetup API key link to the Integrations Import tab.
	 */
	public function should_link_meetup_api_key_to_integrations_import_tab(): void {
		$html = $this->render_imports_tab();

		$expected = tribe( Plugin_Settings::class )->get_url( [ 'tab' => 'integrations-import-tab' ] );
		$this->assertStringContainsString( 'href="' . esc_url( $expected ) . '"', $html );
		$this->assertStringNotContainsString( esc_url( tribe( Plugin_Settings::class )->get_url( [ 'tab' => 'addons' ] ) ), $html );
	}

	/**
	 * @test
	 * It should describe the URL import date range limit without a "Learn more" link.
	 */
	public function should_not_link_url_import_range_to_knowledgebase(): void {
		$html = $this->render_imports_tab();

		$this->assertStringContainsString( 'Selecting a shorter time period may improve results.', $html );
		$this->assertStringNotContainsString( 'url-import-errors-event-aggregator', $html );
	}

	/**
	 * Renders the Imports settings tab with every Event Aggregator section shown.
	 *
	 * @return string The tab HTML.
	 */
	private function render_imports_tab(): string {
		add_filter( 'tec_events_aggregator_show_all_settings', '__return_true' );

		$tab = include tribe( 'tec.main' )->plugin_path . 'src/admin-views/tribe-options-imports.php';
		$this->assertInstanceOf( Settings_Tab::class, $tab );

		ob_start();
		$tab->do_content();

		return ob_get_clean();
	}
}
