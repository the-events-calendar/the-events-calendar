<?php
/**
 * The Events Calendar - Avada Theme Integration Provider.
 *
 * Ensures compatibility between The Events Calendar and the Avada theme.
 * This integration automatically disables Avada’s “Combine Third Party CSS Files”
 * option to prevent stylesheet dependency issues, and appends a contextual note
 * to the Avada Global Options panel explaining the behavior.
 *
 * @since 6.15.8
 *
 * @package TheEventsCalendar\Events\Integrations\Themes\Avada
 */

namespace TEC\Events\Integrations\Themes\Avada;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Theme_Integration;

/**
 * Class Provider
 *
 * Handles compatibility between The Events Calendar and the Avada theme.
 *
 * @since 6.15.8
 *
 * @package TEC\Events\Integrations\Themes\Avada
 */
class Provider extends Integration_Abstract {
	use Theme_Integration;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'avada';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return bool Whether integration should load.
	 */
	public function load_conditionals(): bool {
		// Ensure the active theme or its parent is Avada.
		$theme       = wp_get_theme();
		$parent_name = $theme->parent() ? $theme->parent()->get( 'Name' ) : '';

		// We only need Avada active, not fully initialized yet.
		return in_array( 'Avada', [ $theme->get( 'Name' ), $parent_name ], true );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function load(): void {
		// Hook to run after Avada initializes.

		// [TEC-5682] Temporarily disabling automatic Avada CSS handling until further review.
		// add_action( 'after_setup_theme', [ $this, 'disable_combined_css_option' ], 50 );
		// add_filter( 'avada_options_sections', [ $this, 'append_settings_notice' ], 20 );
	}

	/**
	 * Determines if Avada has been properly initialized.
	 *
	 * Used to ensure Avada's global function and class exist before attempting
	 * to access its settings or modify theme options.
	 *
	 * @since 6.15.8
	 *
	 * @return bool True if Avada is initialized and safe to use, false otherwise.
	 */
	protected function is_avada_setup_correctly(): bool {
		return function_exists( 'Avada' ) || class_exists( '\Avada', false );
	}

	/**
	 * Disables Avada’s “Combine Third Party CSS Files” option for compatibility.
	 *
	 * @since 6.15.8
	 *
	 * @return void
	 */
	public function disable_combined_css_option(): void {
		if ( ! $this->is_avada_setup_correctly() ) {
			return;
		}

		$settings = Avada()->settings ?? null;

		if ( ! $settings || ! is_object( $settings ) || ! $settings->get( 'css_combine_third_party_assets' ) ) {
			// Early bail if already off or we can't access it for some reason.
			return;
		}

		$settings->set( 'css_combine_third_party_assets', false );
	}

	/**
	 * Appends a compatibility note to Avada’s Global Options description.
	 *
	 * @since 6.15.8
	 *
	 * @param array $sections Existing Avada options sections.
	 *
	 * @return array Modified sections array.
	 */
	public function append_settings_notice( array $sections ): array {
		if ( ! $this->is_avada_setup_correctly() ) {
			return $sections;
		}
		if ( empty( $sections['performance']['fields']['css_combine_third_party_assets'] ) ) {
			return $sections;
		}

		$field =& $sections['performance']['fields']['css_combine_third_party_assets'];

		$note_text = sprintf(
			/* translators: %s: Plugin name */
			'<br><strong>%1$s</strong> %2$s',
			esc_html__( 'Note:', 'the-events-calendar' ),
			esc_html__( 'This option is automatically disabled when The Events Calendar is active for compatibility.', 'the-events-calendar' )
		);

		$field['description'] .= $note_text;

		return $sections;
	}
}
