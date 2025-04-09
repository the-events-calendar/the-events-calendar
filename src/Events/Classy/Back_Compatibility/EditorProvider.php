<?php
/**
 * The controller responsible for registering the back-compat editor.
 *
 * @since TBD
 *
 * @package TEC\Events\Classy\Back_Compatibility
 */

declare( strict_types=1 );

namespace TEC\Events\Classy\Back_Compatibility;

use TEC\Common\Contracts\Provider\Controller;

/**
 * Class EditorProvider
 *
 * @since TBD
 *
 * @package TEC\Events\Classy\Back_Compatibility
 */
class EditorProvider extends Controller {

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$back_compatible_editor = new Editor();
		$this->container->singleton( 'editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor.compatibility', $back_compatible_editor );
		$this->container->singleton( 'editor.utils', new Editor_Utils() );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * Bound implementations should not be removed in this method!
	 *
	 * @since TBD
	 *
	 * @return void Filters and actions hooks added by the controller are be removed.
	 */
	public function unregister(): void {
		// Unregister the back-compat editor and utils.
		if ( $this->container->has( 'editor' ) && $this->container->get( 'editor' ) instanceof Editor ) {
			unset( $this->container['editor'] );
			unset( $this->container['events.editor'] );
			unset( $this->container['events.editor.compatibility'] );
		}

		if ( $this->container->has( 'editor.utils' ) && $this->container->get( 'editor.utils' ) instanceof Editor_Utils ) {
			unset( $this->container['editor.utils'] );
		}
	}

	/**
	 * Whether the service provider will be a deferred one or not.
	 *
	 * @return bool
	 */
	public function isDeferred() {
		return true;
	}

	/**
	 * Returns an array of the class or interfaces bound and provided by the service provider.
	 *
	 * @return array<string> A list of fully-qualified implementations provided by the service provider.
	 */
	public function provides() {
		return [
			'editor',
			'events.editor',
			'events.editor.compatibility',
			'editor.utils',
		];
	}
}
