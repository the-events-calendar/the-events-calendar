<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Archive_Events;

/**
 * Class Hooks
 *
 * @since   5.14.2
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.14.2
	 */
	public function register() {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Adds the filters required by the FSE components.
	 *
	 * @since 5.14.2
	 */
	protected function add_filters() {
		add_filter( 'get_block_templates', [ $this, 'filter_include_templates' ], 25, 3 );
		add_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		add_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10, 3 );
		add_filter( 'tribe_settings_save_option_array', [ $this, 'filter_tribe_save_template_option'], 10, 2 );
	}

	/**
	 * Adds the actions required by the FSE components.
	 *
	 * @since 5.14.2
	 */
	protected function add_actions() {
		add_action( 'tribe_editor_register_blocks', [ $this, 'action_register_archive_template' ] );
	}

	/**
	 * Registers the Events Archive template.
	 *
	 * @since 5.14.2
	 */
	public function action_register_archive_template() {
		return $this->container->make( Archive_Events::class )->register();
	}

	/**
	 * Adds the archive template to the array of block templates.
	 *
	 * @since 5.14.2
	 *

	 * @param WP_Block_Template[] $query_result Array of found block templates.
	 * @param array  $query {
	 *     Optional. Arguments to retrieve templates.
	 *
	 *     @type array  $slug__in List of slugs to include.
	 *     @type int    $wp_id Post ID of customized template.
	 * }
	 *
	 *
	 * @return array The modified $query.
	 */
	public function filter_include_templates( $query_result, $query, $template_type ) {
		// Don't load this template in the admin - so it's not editable by users.
		if ( is_admin() ) {
			return $query_result;
		}

		return $this->container->make( Templates::class )->add_events_archive( $query_result, $query, $template_type );
	}

	/**
	 * If we're using a FSE theme, we always use the full styling.
	 *
	 * @since 5.14.2

	 *
	 * @param string  $value The value of the option.
	 * @return string $value The original value, or an empty string if FSE is active.
	 */
	public function filter_events_template_setting_option( $value ) {
		return tec_is_full_site_editor() ? '' : $value;
	}


	/**
	 * Override the get_single_option to return the default event template when FSE is active.
	 *
	 * @since 5.14.2
	 *
	 * @param mixed  $option      Results of option query.
	 * @param string $default     The default value.
	 * @param string $option_name Name of the option.
	 *
	 * @return mixed results of option query.
	 */
	public function filter_tribe_get_single_option( $option, $default, $option_name ) {
		if ( 'tribeEventsTemplate' !== $option_name ) {
			return $option;
		}

		if ( tec_is_full_site_editor() ) {
			return '';
		}

		return $option;
	}

	/**
	 * Overwrite the template option on save if FSE is active.
	 * We only support the default events template for now.
	 *
	 * @since 5.14.2
	 *
	 * @param array<string, mixed> $options   The array of values to save. In the format option key => value.
	 * @param string               $option_id The main option ID.
	 *
	 * @return array<string, mixed> $options   The array of values to save. In the format option key => value.
	 */
	public function filter_tribe_save_template_option( $options, $option_id ) {
		if ( tec_is_full_site_editor() ) {
			$options[ 'tribeEventsTemplate' ] = '';
		}

		return $options;
	}
}
