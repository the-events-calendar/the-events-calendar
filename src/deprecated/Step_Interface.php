<?php
/**
 * File: Step_Interface.php
 */

use TEC\Common\Admin\Onboarding\Steps\Contracts\Step_Interface as Common_Step_Interface;

_deprecated_class( 'TEC\Events\Admin\Onboarding\Steps\Contracts\Step_Interface', 'TBD', 'TEC\Common\Admin\Onboarding\Steps\Contracts\Step_Interface' );

/**
 * Contract for Wizard step processors.
 *
 * @since 6.8.4
 * @deprecated TBD Use TEC\Common\Admin\Onboarding\Steps\Contracts\Step_Interface instead.
 */
interface Step_Interface extends Common_Step_Interface {
}
