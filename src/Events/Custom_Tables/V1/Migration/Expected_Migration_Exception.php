<?php
/**
 * An exception thrown in the context of the migration process. Handled differently than other exceptions, will report
 * error message directly to users.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Expected_Migration_Exception.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Expected_Migration_Exception extends \Exception {

}