<?php
use Tribe\Events\Views\V2\Manager;

/**
 * Registers a view.
 *
 * @since 5.7.0
 * @since 5.10.0 Added route slug parameter that is decoupled from the slug view param.
 *
 * @param string $slug Slug for locating the view file.
 * @param string $name View name.
 * @param string $class View class.
 * @param int $priority View registration priority.
 * @param string $route_slug The slug applied to the route for this view.
 */
function tribe_register_view( $slug, $name, $class, $priority = 50, $route_slug = null) {
	return tribe( Manager::class )->register_view( $slug, $name, $class, $priority, $route_slug );
}

/**
 * Checks whether v2 of the Views is enabled or not.
 *
 * In order the function will check the `TRIBE_EVENTS_V2_VIEWS` constant,
 * the `TRIBE_EVENTS_V2_VIEWS` environment variable and, finally, the `Manager::$option_enabled` option.
 *
 * @since 4.9.2
 *
 * @return bool Whether v2 of the Views are enabled or not.
 */
function tribe_events_views_v2_is_enabled() {
	if ( defined( 'TRIBE_EVENTS_V2_VIEWS' ) ) {
		return (bool) TRIBE_EVENTS_V2_VIEWS;
	}

	$env_var = getenv( 'TRIBE_EVENTS_V2_VIEWS' );
	if ( false !== $env_var ) {
		return (bool) $env_var;
	}

	$enabled = (bool) tribe_get_option( Manager::$option_enabled, false );

	/**
	 * Allows filtering of the Events Views V2 provider, doing so will render
	 * the methods and classes no longer load-able so keep that in mind.
	 *
	 * @since  4.9.2
	 *
	 * @param boolean $enabled Determining if V2 Views is enabled\
	 */
	return apply_filters( 'tribe_events_views_v2_is_enabled', $enabled );
}

/**
 * Checks smart activation of the view v2, is not a function for verification of v2 is active or not.
 *
 * Current only being triggered on plugin activation hook.
 *
 * @since 4.9.13
 *
 * @return bool Wether we just activated the v2 on the database.
 */
function tribe_events_views_v2_smart_activation() {
	/**
	 * Allows filtering of the Events Views V2 smart activation..
	 *
	 * @since  4.9.13
	 *
	 * @param boolean $enabled Determining if V2 Views is enabled\
	 */
	$should_smart_activate = apply_filters( 'tribe_events_views_v2_should_smart_activate', true );

	if ( ! $should_smart_activate ) {
		return false;
	}

	if ( tribe_events_views_v2_is_enabled() ) {
		return false;
	}

	if ( ! tribe_events_is_new_install() ) {
		return false;
	}

	$current_status = tribe_get_option( Manager::$option_enabled, null );

	// Only update when value is either null or empty string.
	if ( null !== $current_status && '' !== $current_status ) {
		return false;
	}

	$status = tribe_update_option( Manager::$option_enabled, true );

	if ( $status ) {
		// Update the default posts_per_page to 12
		tribe_update_option( 'postsPerPage', 12 );

		// Update default events per day on month view amount to 3
		tribe_update_option( 'monthEventAmount', 3 );
	}

	return $status;
}

/**
 * Returns whether the Event Period repository should be used or not.
 *
 * @since 4.9.13
 *
 * @return bool whether the Event Period repository should be used or not.
 */
function tribe_events_view_v2_use_period_repository() {
	$enabled = false;
	if ( defined( 'TRIBE_EVENTS_V2_VIEWS_USE_PERIOD_REPOSITORY' ) ) {
		$enabled = (bool) TRIBE_EVENTS_V2_VIEWS_USE_PERIOD_REPOSITORY;
	}

	$env_var = getenv( 'TRIBE_EVENTS_V2_VIEWS_USE_PERIOD_REPOSITORY' );
	if ( false !== $env_var ) {
		$enabled = (bool) $env_var;
	}
	/**
	 * Filters whether to use the period repository or not.
	 *
	 * @since 4.9.13
	 *
	 * @param boolean $enabled Whether the Event Period repository should be used or not.
	 */
	return (bool) apply_filters( 'tribe_events_views_v2_use_period_repository', $enabled );
}

/**
 * Checks whether to disable V2 widgets.
 *
 * In order the function will check the `TRIBE_EVENTS_WIDGETS_V2_DISABLED` constant,
 * the `TRIBE_EVENTS_WIDGETS_V2_DISABLED` environment variable.
 *
 * Note the internal logic is inverted, as the name of the function is "...is_enabled"
 * while the names of the constant/env_var are "...DISABLED".
 *
 * @since 5.3.0
 *
 * @return bool Whether Widgets v2 should load.
 */
function tribe_events_widgets_v2_is_enabled() {
	// Must have v2 views active.
	if ( ! tribe_events_views_v2_is_enabled() ) {
		return false;
	}

	// If the constant is defined, returns the opposite of the constant.
	if ( defined( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED' ) ) {
		return (bool) ! TRIBE_EVENTS_WIDGETS_V2_DISABLED;
	}

	// Allow env_var to short-circuit for testing.
	$env_var = (bool) getenv( 'TRIBE_EVENTS_WIDGETS_V2_DISABLED' );
	if ( false !== $env_var ) {
		return ! $env_var;
	}

	/**
	 * Allows toggling of the v2 widget views via a filter. Defaults to true.
	 *
	 * @since 5.3.0
	 *
	 * @return boolean Do we enable the widget views?
	 */
	return apply_filters( 'tribe_events_widgets_v2_is_enabled', true );
}

/**
 * Checks whether to disable V2 Single Event styles overrides.
 *
 * In order the function will check the `TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED` constant,
 * the `TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED` environment variable.
 *
 * Note the internal logic is inverted, as the name of the function is "...is_enabled"
 * while the names of the constant/env_var are "...DISABLED".
 *
 * @since 5.5.0
 *
 * @return bool Whether Single Event v2 styles overrides should load.
 */
function tribe_events_single_view_v2_is_enabled() {
	// Must have v2 views active.
	if ( ! tribe_events_views_v2_is_enabled() ) {
		return false;
	}

	// If the constant is defined, returns the opposite of the constant.
	if ( defined( 'TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED' ) ) {
		return (bool) ! TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED;
	}

	// Allow env_var to short-circuit for testing.
	$env_var = (bool) getenv( 'TRIBE_EVENTS_SINGLE_VIEW_V2_DISABLED' );
	if ( false !== $env_var ) {
		return ! $env_var;
	}

	/**
	 * Allows toggling of the single event v2 overrides via a filter. Defaults to true.
	 *
	 * @since 5.5.0
	 *
	 * @return boolean Do we enable the single event styles overrides?
	 */
	return apply_filters( 'tribe_events_single_view_v2_is_enabled', true );
}

/**
 * For legacy usage of the Views V1 we allow removing all notices related to V1 before of Version 6.0.0.
 *
 * @since 5.13.0
 *
 * @todo Once version 6.0.0 is launched this method will be deprecated since all v1 code will be REMOVED.
 *
 * @return bool
 */
function tec_events_views_v1_should_display_deprecated_notice() {
	/**
	 * Allows toggling notices for V1 deprecation via a filter. Defaults to true.
	 *
	 * @since 5.13.0
	 *
	 * @return boolean Disable showing the
	 */
	return (bool) apply_filters( 'tec_events_views_v1_should_display_deprecated_notice', true );
}