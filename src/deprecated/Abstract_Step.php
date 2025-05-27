<?php
/**
 * File: Abstract_Step.php
 */

use TEC\Common\Admin\Onboarding\Steps\Abstract_Step as Common_Abstract_Step;

_deprecated_class( 'TEC\Events\Admin\Onboarding\Steps\Abstract_Step', '6.13.0', 'TEC\Common\Admin\Onboarding\Steps\Abstract_Step' );

/**
 * Abstract step-handler class for the onboarding wizard.
 *
 * @since 6.8.4
 * @deprecated 6.13.0 Use TEC\Common\Admin\Onboarding\Steps\Abstract_Step instead.
 */
abstract class Abstract_Step extends Common_Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.4
	 * @deprecated 6.13.0 Use TEC\Common\Admin\Onboarding\Steps\Abstract_Step::TAB_NUMBER instead.
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 0;
}
