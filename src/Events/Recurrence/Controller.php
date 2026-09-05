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
	 * Whether this plugin provides the Occurrence infrastructure on this site or not.
	 *
	 * This is the pre-boot side of the capability handshake: the full gate as a static
	 * read other plugins (e.g. Events Calendar Pro) can evaluate as early as
	 * `plugins_loaded::1` to decide what to register, before this controller exists and
	 * before the `tec_events_recurrence_registered` action could possibly have fired.
	 * Nothing is constructed: the method reads the kill-switch constant, the environment
	 * variable, the Custom Tables V1 full-activation container var (set on
	 * `tribe_common_loaded`, before `plugins_loaded::1` callbacks run), the
	 * incompatible-Pro governor, the activation option and the runtime filter.
	 *
	 * The option and filter layers are part of the read on purpose: providing the
	 * infrastructure means the feature will actually be ON, not merely shipped. A
	 * consumer that force-enables the feature (Pro's dual-ship switch) must add its
	 * `tec_events_recurrence_enabled` callback BEFORE evaluating this method, so the
	 * answer it acts on is the answer its own callback produces.
	 *
	 * @since TBD
	 *
	 * @return bool Whether this plugin provides the Occurrence infrastructure or not.
	 */
	public static function provides_occurrences(): bool {
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
		 * use this filter to force-enable the feature at `PHP_INT_MAX` priority: with
		 * such a Pro active this filter cannot disable the feature, and the supported
		 * kill-switch is the `TEC_EVENTS_RECURRENCE_DISABLED` constant.
		 *
		 * @since TBD
		 *
		 * @param bool $active Whether the feature is active or not; defaults to the
		 *                     value of the `tec_events_recurrence_active` option.
		 */
		return tribe_is_truthy( apply_filters( 'tec_events_recurrence_enabled', $active ) );
	}

	/**
	 * Whether the feature is active on this site or not.
	 *
	 * Delegates to the static capability read: the two answers are one and the same,
	 * this instance method being the Controller contract's runtime API.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is active or not.
	 */
	public function is_active(): bool {
		return self::provides_occurrences();
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

		if ( $this->container->isBound( All_Occurrences_Provider::class ) ) {
			$this->container->make( All_Occurrences_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Admin_Provider::class ) ) {
			$this->container->make( Admin_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Blocks_Provider::class ) ) {
			$this->container->make( Blocks_Provider::class )->unregister();
		}

		if ( $this->container->isBound( Settings::class ) ) {
			$this->container->make( Settings::class )->unregister();
		}

		// Let a later `register()` run `do_register()` again: the base contract only ever sets this flag.
		$this->container->setVar( self::class . '_registered', false );
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
		 * re-attaches their hooks; each provider registration is idempotent. Binding is
		 * guarded: re-binding a resolved singleton would orphan the instance carrying
		 * the attached hooks.
		 */
		$providers = [
			Engine_Provider::class,
			Frontend_Provider::class,
			Views_Provider::class,
			All_Occurrences_Provider::class,
			Admin_Provider::class,
			Blocks_Provider::class,
			Settings::class,
		];

		foreach ( $providers as $provider ) {
			if ( ! $this->container->isBound( $provider ) ) {
				$this->container->singleton( $provider );
			}

			$this->container->make( $provider )->register();
		}
	}
}
