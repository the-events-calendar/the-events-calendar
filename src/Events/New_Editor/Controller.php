<?php
/**
 * The main controller responsible for the New Editor feature.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */

namespace TEC\Events\New_Editor;

use TEC\Common\Contracts\Provider\Controller as ControllerContract;
use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main as TEC;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\New_Editor;
 */
class Controller extends ControllerContract {
	/**
	 * Returns whether this feature is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool True if not deactivated via environment variable or a constant.
	 */
	public function is_active(): bool {
		return tec_using_new_editor();
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

		// We're replacing the editor and loading the Block Editor in a controller mode.
		add_filter( 'replace_editor', [ $this, 'replace_editor' ], 20, 2 );

		// Tell Common, TEC, ET and so on NOT to load blocks.
		add_filter( 'tribe_editor_should_load_blocks', '__return_false' );

		// We're using TEC new editor.
		add_filter( 'tec_using_new_editor', '__return_true' );

		// Register the main assets entry point.
		Asset::add(
			'tec-new-editor',
			'new-editor.js'
		)->add_to_group_path( TEC::class . '-packages' )
		     ->add_to_group( 'tec-new-editor' )
		     ->enqueue_on( 'enqueue_block_editor_assets' )
			->add_localize_script('tec.newEditor', [
				'eventCategoryTaxonomyName' => TEC::TAXONOMY,
			])
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
		remove_filter( 'replace_editor', [ $this, 'replace_editor' ], 20 );
		remove_filter( 'tribe_editor_should_load_blocks', '__return_false' );
		remove_filter( 'tec_using_new_editor', '__return_true' );
	}

	/**
	 * Filters the WordPress `replace_editor` filter to take over and loads the Block Editor in a controlle mode.
	 *
	 * @since TBD
	 *
	 * @param bool $replace_editor Whether to replace the chosen editor or not.
	 * @param WP_Post $post        The post being edited. A new post auto-draft for new posts, an existing post object for updates.
	 *
	 * @return bool Whether to replace the editor or not.
	 */
	public function replace_editor( bool $replace_editor, \WP_Post $post ): bool {
		// No need to flag that we're using the Block Editor: requiring the main Block Editor file will do that for us.
		require ABSPATH . 'wp-admin/edit-form-blocks.php';

		return true;
	}
}
