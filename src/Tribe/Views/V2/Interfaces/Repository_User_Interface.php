<?php
/**
 * Classes implementing this interface will provide methods to set and get a repository instance.
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Interfaces
 */

namespace Tribe\Events\Views\V2\Interfaces;

use Tribe\Events\Views\V2\View;
use Tribe__Repository__Interface as Repository;

/**
 * Class Repository_User_Interface
 *
 * @since   4.9.3
 * @package Tribe\Events\Views\V2\Interfaces
 */
interface Repository_User_Interface {

	/**
	 * Sets the repository the instance should use.
	 *
	 * @since 4.9.3
	 *
	 * @param  Repository  $repository The repository object the instance should use or `null` to unset it.
	 */
	public function set_repository( Repository $repository = null );

	/**
	 * Returns the repository currently used by the instance, if any.
	 *
	 * @since 4.9.3
	 *
	 * @return Repository|null The repository instance used by the instance or `null` if the instance is not using a
	 *                         repository.
	 */
	public function get_repository(  );

	/**
	 * Restores the loop variables by restoring the global query.
	 *
	 * @since 4.9.3
	 */
	public function restore_the_loop();
}