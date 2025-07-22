<?php
/**
 * Category Colors Settings.
 *
 * This file manages the settings for Category Colors, including registering a settings tab
 * and rendering the necessary fields within the admin interface.
 *
 * @since 6.14.0
 * @package TEC\Events\Category_Colors\Settings
 */

namespace TEC\Events\Category_Colors\Settings;

use Tribe__Settings_Tab;
use Tribe\Events\Admin\Settings as Admin_Settings;
use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Plain_Text;
use TEC\Common\Admin\Entities\H3;
use Tribe\Utils\Element_Classes;
use TEC\Common\Admin\Entities\Paragraph;
use Tribe\Events\Views\V2\Manager;

/**
 * Class Settings
 *
 * Handles the settings for Category Colors, including registering the settings tab
 * and rendering the associated fields.
 *
 * @since 6.14.0
 */
class Settings {

	/**
	 * Tab name identifier.
	 *
	 * Used to register and identify the settings tab for category colors.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	public static string $tab_slug = 'display-category-colors-settings';

	/**
	 * Registers necessary hooks for modifying settings and fields.
	 *
	 * This method adds an action to register a custom settings tab and a filter
	 * to modify the legend label for the "Legend Superpowers" checkbox field.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'tec_events_settings_tab_display', [ $this, 'register_tab' ] );
		add_filter( 'tribe_field_start', [ $this, 'customize_legend_superpowers_label' ], 10, 2 );
	}

	/**
	 * Unregisters the hooks added by this class.
	 *
	 * This method removes the previously registered action and filter
	 * to ensure they do not persist when the functionality is disabled.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function unregister_hooks() {
		remove_action( 'tribe_settings_do_tabs', [ $this, 'register_tab' ] );
		remove_filter( 'tribe_field_start', [ $this, 'customize_legend_superpowers_label' ] );
	}

	/**
	 * Customizes the "Legend Superpowers" checkbox field by appending additional tooltip text.
	 *
	 * This method modifies the field structure by injecting a description paragraph
	 * after the `<legend>` tag for better clarity.
	 *
	 * @since 6.14.0
	 *
	 * @param string $field_start The starting HTML for the field.
	 * @param string $field_id    The ID of the field.
	 *
	 * @return string Modified field start HTML with additional description.
	 */
	public function customize_legend_superpowers_label( string $field_start, string $field_id ): string {
		if ( 'category-color-legend-superpowers' !== $field_id ) {
			return $field_start;
		}

		$tooltip_text = esc_html__( 'This feature helps your users highlight events belonging to a specific category.', 'the-events-calendar' );

		return $field_start . sprintf(
			'<p class="tooltip description">%s</p>',
			$tooltip_text
		);
	}

	/**
	 * Registers the Category Colors tab to the settings page.
	 *
	 * @since 6.14.0
	 *
	 * @param Tribe__Settings_Tab $display_tab The display settings tab.
	 *
	 * @return void
	 */
	public function register_tab( Tribe__Settings_Tab $display_tab ): void {
		// Only load the tab for event settings, not ticket settings.
		if ( ! tribe( Admin_Settings::class )->is_tec_events_settings() ) {
			return;
		}

		// Create the tab instance under "Display".
		$display_category_colors_tab = new Tribe__Settings_Tab(
			self::$tab_slug,
			esc_html__( 'Category Colors', 'the-events-calendar' ),
			[
				'priority' => 5.30,
				'fields'   => apply_filters(
					'tec_events_settings_display_category_colors_section',
					$this->generate_settings()
				),
			]
		);

		/**
		 * Fires after the display category colors settings tab has been created.
		 *
		 * @since 6.14.0
		 *
		 * @param Tribe__Settings_Tab $display_category_colors_tab The display category colors settings tab.
		 */
		do_action( 'tec_events_settings_tab_display_category_colors', $display_category_colors_tab );

		$display_tab->add_child( $display_category_colors_tab );
	}

	/**
	 * Generates the settings for the Category Colors feature.
	 *
	 * @since 6.14.0
	 * @since 6.14.2 Added the "Enabled" setting, and updated the Reset button tooltip.
	 *
	 * @return array The structured settings array for Category Colors.
	 */
	public function generate_settings(): array {
		/** @var Manager $manager */
		$manager = tribe( Manager::class );

		$category_color_title = new Div( new Element_Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] ) );
		$category_color_title->add_child(
			new H3(
				_x( 'Category Colors', 'Category Colors section header', 'the-events-calendar' ),
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
					new H3(
						_x( 'Category Legend', 'Category Legend settings section header', 'the-events-calendar' ),
						new Element_Classes( [ 'tec-settings-form__section-header' ] )
					),
					( new Plain_Text( esc_html__( 'The category legend provides labels for the colors that appear on your events on event listing pages.', 'the-events-calendar' ) ) ),
				]
			),
			'category-color-enable-frontend'        => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Enable', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable the frontend functionality for Category Colors.', 'the-events-calendar' ),
				'default'         => true,
				'validation_type' => 'boolean',
			],
			'category-color-legend-show'            => [
				'type'            => 'checkbox_list',
				'label'           => __( 'Show Category Legend in these Event Views', 'the-events-calendar' ),
				'default'         => array_keys( $manager->get_publicly_visible_views() ),
				'validation_type' => 'options_multi',
				'options'         => array_map(
					static function ( $view ) use ( $manager ) {
						return $manager->get_view_label_by_class( $view );
					},
					$manager->get_publicly_visible_views( false )
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
				'tooltip'         => esc_html__( "Display categories in the legend even if they aren't currently shown on the calendar.", 'the-events-calendar' ),
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
				'tooltip'         => esc_html__( 'Only appears if Legend Superpowers are active. Helps users clear their category selections.', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			],
		];

		$category_colors_section = tribe( 'settings' )->wrap_section_content( 'tec-events-settings-calendar-template', $category_colors_section );

		return array_merge( $category_color, $category_colors_section );
	}
}
