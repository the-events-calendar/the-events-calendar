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
 * @since 6.0.0 Deprecate function.
 *
 * @return bool Whether v2 of the Views are enabled or not.
 */
function tribe_events_views_v2_is_enabled() {
	/**
	 * Allows filtering of the Events Views V2 provider, doing so will render
	 * the methods and classes no longer load-able so keep that in mind.
	 *
	 * @since 4.9.2
	 * @since 6.0.0 Deprecate filter.
	 *
	 * @deprecated 6.0.0
	 *
	 * @param boolean $enabled Determining if V2 Views is enabled
	 */
	apply_filters_deprecated( 'tribe_events_views_v2_is_enabled', [ true ], '6.0.0', 'No replacement. Legacy views have been removed.' );

	return true;
}

/**
 * Checks add loads default options for our settings.
 * Current only being triggered on plugin activation hook.
 *
 * @since 6.0.0
 *
 * @return bool  Whether initializer ran or not.
 */
function tribe_events_settings_defaults_initializer() {
	// Seems to only check if we have had a previous version installed
	if ( ! tribe_events_is_new_install() ) {
		return false;
	}

	$default_options = [
		'dateWithYearFormat'       => 'F j, Y',
		'recurrenceMaxMonthsAfter' => 24,
	];

	$default_options[ Tribe__Events__Google__Maps_API_Key::$api_key_option_name ] = Tribe__Events__Google__Maps_API_Key::$default_api_key;

	/**
	 * Allows filtering of the settings defaults on activation.
	 *
	 * @since  6.0.0
	 *
	 * @param array $default_options
	 */
	$default_options = apply_filters( 'tribe_events_settings_default_fields_initializer', $default_options );

	if ( empty( $default_options ) ) {
		return false;
	}

	$options = Tribe__Settings_Manager::get_options();
	foreach ( $default_options as $field => $default_value ) {
		// Only update when value is not set
		if ( isset( $options[ $field ] ) ) {
			continue;
		}

		// Save our default
		tribe_update_option( $field, $default_value );
	}

	return true;
}

/**
 * Checks smart activation of the view v2, is not a function for verification of v2 is active or not.
 *
 * Current only being triggered on plugin activation hook.
 *
 * @since 4.9.13
 * @since 6.0.0 Deprecate function.
 *
 * @deprecated 6.0.0
 *
 * @return bool Whether we just activated the v2 on the database.
 */
function tribe_events_views_v2_smart_activation() {
	return false;
}

/**
 * Returns whether the Event Period repository should be used or not.
 *
 * @since 4.9.13
 * @since 6.0.0 Deprecate function.
 *
 * @deprecated 6.0.0
 *
 * @return bool whether the Event Period repository should be used or not.
 */
function tribe_events_view_v2_use_period_repository() {
	/**
	 * Filters whether to use the period repository or not.
	 *
	 * @since 4.9.13
	 * @since 6.0.0 Deprecate filter.
	 *
	 * @deprecated 6.0.0
	 *
	 * @param boolean $enabled Whether the Event Period repository should be used or not.
	 */
	apply_filters_deprecated( 'tribe_events_views_v2_use_period_repository', [ false ], '6.0.0', 'No replacement. Period repository never in use.' );

	return false;
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
 * @since 6.0.0 Deprecate function.
 *
 * @return bool Whether Widgets v2 should load.
 */
function tribe_events_widgets_v2_is_enabled() {
	/**
	 * Allows toggling of the v2 widget views via a filter. Defaults to true.
	 *
	 * @since 5.3.0
	 * @since 6.0.0 Deprecate filter.
	 *
	 * @deprecated 6.0.0
	 *
	 * @param boolean $enabled Determining if V2 Views is enabled
	 */
	apply_filters_deprecated( 'tribe_events_widgets_v2_is_enabled', [ true ], '6.0.0', 'No replacement. Legacy views have been removed.' );

	return true;
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
 * @since 6.0.0 Deprecate function.
 *
 * @return bool Whether Single Event v2 styles overrides should load.
 */
function tribe_events_single_view_v2_is_enabled() {
	/**
	 * Allows toggling of the single event v2 overrides via a filter. Defaults to true.
	 *
	 * @since 5.5.0
	 * @since 6.0.0 Deprecate filter.
	 *
	 * @deprecated 6.0.0
	 *
	 * @return boolean Do we enable the single event styles overrides?
	 */
	apply_filters_deprecated( 'tribe_events_single_view_v2_is_enabled', [ true ], '6.0.0', 'No replacement. Legacy views have been removed.' );

	return true;
}

/**
 * For legacy usage of the Views V1 we allow removing all notices related to V1 before of Version 6.0.0.
 *
 * @since 5.13.0
 *
 * @return bool
 */
function tec_events_views_v1_should_display_deprecated_notice() {
	/**
	 * Allows toggling notices for V1 deprecation via a filter. Defaults to true.
	 *
	 * @since 5.13.0
	 * @since 6.0.0 Deprecated filter.
	 *
	 * @deprecated 6.0.0
	 *
	 * @return boolean Disable showing the
	 */
	apply_filters_deprecated( 'tec_events_views_v1_should_display_deprecated_notice', [ true ], '6.0.0', 'No replacement. Legacy views have been removed.' );

	return false;
}