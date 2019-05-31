<?php
use Tribe\Events\Views\V2\View as View;

/**
 * Checks whether v2 of the Views is enabled or not.
 *
 * In order the function will check the `TRIBE_EVENTS_V2_VIEWS` constant,
 * the `TRIBE_EVENTS_V2_VIEWS` environment variable and, finally, the `static::$option_enabled` option.
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

	$enabled = (bool) tribe_get_option( View::$option_enabled, false );

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