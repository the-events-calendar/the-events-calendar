<?php

use Spatie\Snapshots\MatchesSnapshots;
use Tribe__Customizer as Customizer;

class CustomizerSettingsTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * It should return empty with an empty Customizer.
	 *
	 * @test
	 */
	public function should_return_empty_with_an_empty_customizer() {
		$css_template = tribe('customizer')->get_styles_scripts();

		$this->assertMatchesSnapshot( $css_template );
	}

	/**
	 * It should allow taking a CSS template snapshot with provided styles.
	 *
	 * @test
	 */
	public function should_allow_taking_a_css_template_snapshot_if_given_data() {
		// Pass some specific styles via a filter.
		add_filter(
			'tribe_customizer_css_template',
			function() {
				return ":root {
						/* Customizer-added Global Event styles */
						--tec-color-link-primary: #238923;
					    --tec-color-link-accent: #238923;
					    --tec-color-link-accent-hover: rgba(35,137,35, 0.8);
					    --tec-color-accent-primary: #238923;
					    --tec-color-accent-primary-hover: rgba(35,137,35,0.8);
					    --tec-color-accent-primary-multiday: rgba(35,137,35,0.24);
					    --tec-color-accent-primary-multiday-hover: rgba(35,137,35,0.34);
					    --tec-color-accent-primary-active: rgba(35,137,35,0.9);
					    --tec-color-accent-primary-background: rgba(35,137,35,0.07);
					    --tec-color-background-secondary-datepicker: rgba(35,137,35,0.5);
					    --tec-color-accent-primary-background-datepicker: #238923;
					    --tec-color-button-primary: #238923;
					    --tec-color-button-primary-hover: rgba(35,137,35,0.8);
					    --tec-color-button-primary-active: rgba(35,137,35,0.9);
					    --tec-color-button-primary-background: rgba(35,137,35,0.07);
					    --tec-color-day-marker-current-month: #238923;
					    --tec-color-day-marker-current-month-hover: rgba(35,137,35,0.8);
					    --tec-color-day-marker-current-month-active: rgba(35,137,35,0.9);
					    --tec-color-background-primary-multiday: rgba(35,137,35, 0.24);
					    --tec-color-background-primary-multiday-hover: rgba(35,137,35, 0.34);
					    --tec-color-background-primary-multiday-active: rgba(35,137,35, 0.34);
					    --tec-color-background-secondary-multiday: rgba(35,137,35, 0.24);
					    --tec-color-background-secondary-multiday-hover: rgba(35,137,35, 0.34);
					}";
			}
		);

		$css_template = tribe('customizer')->get_styles_scripts();

		$this->assertMatchesSnapshot( $css_template );
	}

	/**
	 * It should allow taking a CSS template snapshot from the database.
	 *
	 * @test
	 */
	public function it_should_allow_taking_a_snapshot_from_database() {
		// Add Customizer settings to the database.
		$data = [
			"global_elements" => [
				"font_size" => "1",
				"font_size_base" => "24",
				"font_family" => "theme"
			],
			"tec_events_bar" => [
				"events_bar_border_color_choice" => "custom",
				"events_bar_text_color" => "#8224e3",
				"find_events_button_color_choice" => "custom",
				"find_events_button_color" => "#dd3333",
				"events_bar_border_color" => "#8224e3"
			],
			"month_view" => [
				"grid_background_color_choice" => "custom",
				"grid_background_color" => "#ddb982",
				"multiday_event_bar_color_choice" => "custom",
				"multiday_event_bar_color" => "#c329d1"
			],
			"single_event" => [
				"post_title_color_choice" => "custom",
				"post_title_color" => "#eeee22"
			],
		];

		update_option( 'tribe_customizer', $data, true );

		$css_template = tribe('customizer')->get_styles_scripts();

		$this->assertMatchesSnapshot( $css_template );

		update_option( 'tribe_customizer', '' );
	}
}
