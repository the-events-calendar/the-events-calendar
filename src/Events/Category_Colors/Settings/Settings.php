<?php

namespace TEC\Events\Category_Colors\Settings;

use Tribe__Field;
use Tribe__Settings_Tab;
use Tribe\Events\Admin\Settings as Admin_Settings;
use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Field_Wrapper;
use TEC\Common\Admin\Entities\Heading;
use Tribe\Utils\Element_Classes;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;
use Tribe\Events\Views\V2\Manager;

/**
 * Class Settings
 *
 * Handles the settings for Category Colors, including registering the settings tab
 * and rendering the associated fields.
 *
 * @since TBD
 */
class Settings {

	/**
	 * Tab name identifier.
	 *
	 * Used to register and identify the settings tab for category colors.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $tab_slug = 'category-colors-settings';

	/**
	 * Registers necessary hooks for modifying settings and fields.
	 *
	 * This method adds an action to register a custom settings tab and a filter
	 * to modify the legend label for the "Legend Superpowers" checkbox field.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'tribe_settings_do_tabs', [ $this, 'register_tab' ] );
		add_filter( 'tribe_field_start', [ $this, 'customize_legend_superpowers_label' ], 10, 2 );
	}

	/**
	 * Customizes the "Legend Superpowers" checkbox field by appending additional tooltip text.
	 *
	 * This method modifies the field structure by injecting a description paragraph
	 * after the `<legend>` tag for better clarity.
	 *
	 * @since TBD
	 *
	 * @param string $field_start The starting HTML for the field.
	 * @param string $field_id    The ID of the field.
	 *
	 * @return string Modified field start HTML with additional description.
	 */
	public function customize_legend_superpowers_label( string $field_start, string $field_id ): string {
		if ( 'category-color-legend-superpowers' === $field_id ) {
			$tooltip_text = esc_html__( 'This feature helps your users highlight events belonging to a specific category.', 'the-events-calendar' );

			$field_start .= sprintf(
				'<p class="tooltip description">%s</p>',
				$tooltip_text
			);
		}

		return $field_start;
	}

	/**
	 * Registers the Category Colors tab to the settings page.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_tab(): void {
		// Only load the tab for event settings, not ticket settings.
		if ( ! tribe( Admin_Settings::class )->is_tec_events_settings() ) {
			return;
		}

		new Tribe__Settings_Tab(
			self::$tab_slug,
			esc_html__( 'Category Colors', 'the-events-calendar' ),
			[
				'fields' => $this->generate_settings(),
			]
		);
	}

	/**
	 * Generates the settings for the Category Colors feature.
	 *
	 * @since TBD
	 *
	 * @return array The structured settings array for Category Colors.
	 */
	public function generate_settings(): array {
		$category_color_title = new Div( new Element_Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] ) );
		$category_color_title->add_child(
			new Heading(
				_x( 'Category Colors', 'Category Colors section header', 'the-events-calendar' ),
				3,
				new Element_Classes( 'tec-settings-form__section-header' )
			)
		);
		$category_color_title->add_child(
			( new Paragraph( new Element_Classes( 'tec-settings-form__section-description' ) ) )->add_children(
				[
					new Plain_Text( __( 'Category colors helps your users relate events to a specific category on the event listing pages. To assign colors to event categories see Event Categories page.', 'the-events-calendar' ) ),
				]
			)
		);

		$category_color          = [
			'tec-events-category-colors' => $category_color_title,
		];
		$category_colors_section = [
			'category-color-header'                 => ( new Div( new Element_Classes( [ 'tec-settings-form__header-block' ] ) ) )->add_children(
				[
					new Heading(
						_x( 'Category Legend', 'Category Legend settings section header', 'the-events-calendar' ),
						2,
						new Element_Classes( [ 'tec-settings-form__section-header' ] )
					),
					( new Field_Wrapper(
						new Tribe__Field(
							'categoryLegendExplanation',
							[
								'type' => 'html',
								'html' => esc_html__( 'The category legend provides labels for the colors that appear on your events on event listing pages.', 'the-events-calendar' ),
							]
						)
					) ),
				]
			),
			'category-color-legend-show'            => [
				'type'            => 'checkbox_list',
				'label'           => __( 'Show Category Legend in these Event Views', 'the-events-calendar' ),
				'default'         => array_keys( tribe( Manager::class )->get_publicly_visible_views() ),
				'validation_type' => 'options_multi',
				'options'         => array_map(
					static function ( $view ) {
						return tribe( Manager::class )->get_view_label_by_class( $view );
					},
					tribe( Manager::class )->get_publicly_visible_views( false )
				),
			],
			'category-color-legend-superpowers'     => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Legend Superpowers', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
			'category-color-show-hidden-categories' => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Show hidden categories in legend', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Show only the next event in each Series', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
			'category-color-custom-css'             => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Custom CSS', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Use your own CSS for category legend', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
			'category-color-reset-button'           => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Reset Button', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
		];

		$category_colors_section = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-calendar-template', $category_colors_section );

		return array_merge( $category_color, $category_colors_section );
	}
}
