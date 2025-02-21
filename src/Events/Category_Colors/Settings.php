<?php

namespace TEC\Events\Category_Colors;

use Tribe__Field;
use Tribe__Settings_Tab;
use Tribe\Events\Admin\Settings as Admin_Settings;
use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Field_Wrapper;
use TEC\Common\Admin\Entities\Heading;
use Tribe\Utils\Element_Classes;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;

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
	public static string $tab_slug = 'category-colors';

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
								'html' => __( 'The category legend provides labels for the colors that appear on your events on event listing pages.', 'the-events-calendar' ),
							]
						)
					) ),
				]
			),
			// @todo Figure out a way to do this label properly, and translate it!
			'category-color-legend-superpowers'     => [
				'type'            => 'checkbox_bool',
				'label'           => 'Legend Superpowers <br/> This feature helps your users hightlight events belonging to a specific category.',
				'tooltip'         => esc_html__( 'This feature helps your users highlight events belonging to a specific category.', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
			'category-color-show-hidden-categories' => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Show hidden categories in legend', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Show only the next event in each Series. *This needs text*', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
			'category-color-custom-CSS'             => [
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
