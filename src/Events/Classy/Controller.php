<?php
/**
 * The main controller responsible for the New Editor feature.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use TEC\Common\Contracts\Provider\Controller as ControllerContract;
use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main as TEC;
use WP_Block_Editor_Context;
use WP_Post;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends ControllerContract {
	/**
	 * The name of the constant that will be used to disable the feature.
	 * Setting it to a truthy value will disable the feature.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DISABLED = 'TEC_CLASSY_EDITOR_DISABLED';

	/**
	 * Detects, based on constants, environment variables whether the feature is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is active or not.
	 */
	private static function is_feature_active(): bool {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The constant to disable the feature is defined and it's truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The environment variable to disable the feature is truthy.
			return false;
		}

		// Finally read an option value to determine if the feature should be active or not.
		$active = (bool) get_option( 'tec_events_classy_editor_enabled', true );

		/**
		 * Allows filtering whether the whole Seating feature
		 * should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since TBD
		 *
		 * @param bool $active Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_events_classy_editor_enabled', $active );
	}

	/**
	 * Returns true.
	 *
	 * The purpose of this method is to provide a uniquely identifiable method to be used in filters.
	 * This will allow removing the method hooked by this provider from filters, in place of removing
	 * a generic `__return_true` that might have been added by some other code.
	 *
	 * @since TBD
	 *
	 * @return true The boolean value `true`.
	 */
	public static function return_true(): bool {
		return true;
	}

	/**
	 * Return false.
	 *
	 * The purpose of this method is to provide a uniquely identifiable method to be used in filters.
	 * This will allow removing the method hooked by this provider from filters, in place of removing
	 * a generic `__return_false` that might have been added by some other code.
	 *
	 * @since TBD
	 *
	 * @return false The boolean value `false`.
	 */
	public static function return_false(): bool {
		return false;
	}

	/**
	 * Registers required filters early, before other plugins load.
	 *
	 * The main function of this code is to filter the template tag that lets other plugins
	 * and controllers know whether the Classy editor is being used or not.
	 * This function is used in the `Tribe__Events__Main::plugins_loaded` method.
	 *
	 * @since TBD
	 *
	 * @return void The Classy template tag is filtered accordingly.
	 * @see   \Tribe__Events__Main::plugins_loaded()
	 */
	public static function early_register(): void {
		if ( ! self::is_feature_active() ) {
			add_filter( 'tec_using_classy_editor', [ self::class, 'return_false' ] );

			return;
		}

		add_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );
	}

	/**
	 * Determines if the feature is enabled or not.
	 *
	 * Since this class `early_register` method is already filtering the `tec_using_classy_editor` template
	 * tag, this method will call the template tag to know whether it should activate or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is enabled or not.
	 */
	public function is_active(): bool {
		return tec_using_classy_editor();
	}

	/**
	 * Binds the implementations required by the feature and hooks the controller to the actions and filters required.
	 *
	 * @since TBD
	 *
	 * @return void Bindings are registered, the controller is hooked to actions and filters.
	 */
	protected function do_register(): void {
		// Register the `editor` binding replacement for back-compatibility purposes.
		$back_compatible_editor = new Back_Compatible_Editor();
		$this->container->singleton( 'editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor.compatibility', $back_compatible_editor );

		// Tell Common, TEC, ET and so on NOT to load blocks.
		add_filter( 'tribe_editor_should_load_blocks', '__return_false' );

		// We're using TEC new editor.
		add_filter( 'tec_using_classy_editor', '__return_true' );

		add_filter( 'block_editor_settings_all', [ $this, 'filter_block_editor_settings' ], 100, 2 );

		// Register the main assets entry point.
		Asset::add(
			'tec-classy',
			'classy.js'
		)->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-classy' )
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( fn() => $this->post_uses_new_editor( get_post_type() ) )
			->add_localize_script( 'tec.events.classy.data', [ $this, 'get_data' ] )
			->register();

		Asset::add(
			'tec-classy-style',
			'style-classy.css'
		)->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-classy' )
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( fn() => $this->post_uses_new_editor( get_post_type() ) )
			->register();
	}

	/**
	 * Unhooks the controller from the actions and filters required by the feature.
	 *
	 * @since TBD
	 *
	 * @return void The hooked actions and filters are removed.
	 */
	public function unregister(): void {
		remove_filter( 'tribe_editor_should_load_blocks', '__return_false' );
		remove_filter( 'tec_using_classy_editor', '__return_true' );
		remove_filter( 'block_editor_settings_all', [ $this, 'filter_block_editor_settings' ], 100 );
		remove_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );
		remove_filter( 'tribe_editor_should_load_blocks', [ self::class, 'return_false' ] );
	}

	/**
	 * Returns whether the given Post uses the New Editor.
	 *
	 * @since TBD
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool Whether the given Post uses the New Editor.
	 */
	public function post_uses_new_editor( string $post_type ): bool {
		$supported_post_types = $this->get_supported_post_types();

		return in_array( $post_type, $supported_post_types, true );
	}

	/**
	 * Filters the Block Editor Settings for a given Post Type to lock the template.
	 *
	 * @since TBD
	 *
	 * @param array<string,string>    $settings The Block Editor settings.
	 * @param WP_Block_Editor_Context $context
	 *
	 * @return array<string,string> The updated Block Editor settings.
	 */
	public function filter_block_editor_settings( array $settings, WP_Block_Editor_Context $context ) {
		if ( ! (
			$context->post instanceof WP_Post
			&& $this->post_uses_new_editor( $context->post->post_type )
		) ) {
			return $settings;
		}

		// Lock the template.
		$settings['templateLock'] = true;

		return $settings;
	}

	/**
	 * Returns the filtered list of Post Types that should be using the New Editor.
	 *
	 * @since TBD
	 *
	 * @return list<string> The filtered list of Post Types that should be using the New Editor.
	 */
	private function get_supported_post_types(): array {
		/**
		 * Filters the list of post types that use the new editor.
		 *
		 * @since TBD
		 *
		 * @param array<string> $supported_post_types The list of post types that use the new editor.
		 */
		$supported_post_types = apply_filters(
			'tec_events_classy_post_types',
			[ TEC::POSTTYPE ]
		);

		return (array) $supported_post_types;
	}

	/**
	 * Returns the data that is localized on the page for the Classy app.
	 *
	 * @since TBD
	 *
	 * @return array{
	 *   eventCategoryTaxonomyName: string,
	 * }
	 */
	public function get_data(): array {
		return [
			'eventCategoryTaxonomyName' => TEC::TAXONOMY,
		];
	}
}
