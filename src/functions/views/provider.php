<?php
use Tribe\Events\Views\V2\Manager;

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
 * Current only being triggered on plugin actiovation hook.
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
