<?php
/**
 * Manages the Recurrence feature: Events with multiple Occurrences in the free plugin.
 *
 * The controller is the single on-switch for the feature and the capability handshake
 * other plugins (e.g. Events Calendar Pro, Event Tickets) use to detect that the free
 * plugin provides the Occurrence infrastructure.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Controller extends Controller_Contract {
	/**
	 * The name of the constant, or environment variable, that will disable the feature
	 * when defined and truthy, no matter the option or filter values.
	 *
	 * @since TBD
	 */
	public const DISABLED = 'TEC_EVENTS_RECURRENCE_DISABLED';

	/**
	 * The version of the free Recurrence feature contract; the capability handshake
	 * other plugins can version-check against.
	 *
	 * @since TBD
	 */
	public const VERSION = '1.0.0';

	/**
	 * The name of the option that will store the feature activation state.
	 *
	 * @since TBD
	 */
	public const ACTIVE_OPTION = 'tec_events_recurrence_active';

	/**
	 * The minimum Events Calendar Pro version this feature can be active alongside.
	 *
	 * Older Pro versions ship their own copy of the Occurrence infrastructure: when one
	 * is active, the feature yields entirely and the older Pro keeps sole ownership.
	 *
	 * @since TBD
	 */
	public const MINIMUM_PRO_VERSION = '7.9.0-dev';

	/**
	 * The action fired when the controller registers, the registration side of the
	 * capability handshake.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $registration_action = 'tec_events_recurrence_registered';

	/**
	 * Whether the feature is active on this site or not.
	 *
	 * The gate layers, in order of precedence: the kill-switch constant, the environment
	 * variable, the Custom Tables V1 full-activation precondition, the incompatible-Pro
	 * governor, the activation option, and finally the runtime filter.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is active or not.
	 */
	public function is_active(): bool {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The constant to disable the feature is defined and truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The environment variable to disable the feature is truthy.
			return false;
		}

		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			/*
			 * Occurrences only make sense on top of the Custom Tables V1 storage: no
			 * feature on sites where CT1 is disabled or not migrated yet.
			 */
			return false;
		}

		if ( self::is_incompatible_pro_active() ) {
			// An older Events Calendar Pro owns the Occurrence infrastructure: yield.
			return false;
		}

		$active = (bool) get_option( self::ACTIVE_OPTION, false );

		/**
		 * Filters whether the free Recurrence (Occurrences) feature is active or not.
		 *
		 * Events Calendar Pro versions that build on the free Occurrence infrastructure
		 * use this filter to force-enable the feature.
		 *
		 * @since TBD
		 *
		 * @param bool $active Whether the feature is active or not; defaults to the
		 *                     value of the `tec_events_recurrence_active` option.
		 */
		return (bool) apply_filters( 'tec_events_recurrence_enabled', $active );
	}

	/**
	 * Whether an Events Calendar Pro version too old to build on this feature is active.
	 *
	 * @since TBD
	 *
	 * @return bool Whether an incompatible Events Calendar Pro version is active or not.
	 */
	public static function is_incompatible_pro_active(): bool {
		if ( ! class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			// No Pro, or Pro did not boot: nothing to yield to.
			return false;
		}

		return version_compare( \Tribe__Events__Pro__Main::VERSION, self::MINIMUM_PRO_VERSION, '<' );
	}

	/**
	 * Registers the feature sub-controllers and the capability handshake state.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->setVar( 'tec_events_recurrence_fully_activated', true );

		/*
		 * The sub-providers are singletons registered directly: unlike a container-level
		 * provider registration, a register/unregister/register cycle (e.g. in tests)
		 * re-attaches their hooks; each provider registration is idempotent.
		 */
		$this->container->singleton( Engine_Provider::class );
		$this->container->make( Engine_Provider::class )->register();
		$this->container->singleton( Frontend_Provider::class );
		$this->container->make( Frontend_Provider::class )->register();
		$this->container->singleton( Views_Provider::class );
		$this->container->make( Views_Provider::class )->register();
		$this->container->singleton( Admin_Provider::class );
		$this->container->make( Admin_Provider::class )->register();

		// Further sub-controllers (Settings) register here as they land.
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->setVar( 'tec_events_recurrence_fully_activated', false );

		if ( $this->container->isBound( Engine_Provider::class ) ) {
			$this->container->make( Engine_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Frontend_Provider::class ) ) {
			$this->container->make( Frontend_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Views_Provider::class ) ) {
			$this->container->make( Views_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Admin_Provider::class ) ) {
			$this->container->make( Admin_Provider::class )->unregister();
		}
	}
}
