<?php
/**
 * Manages integration between WPML and Views v2 filters of The Events Calendar.
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Integrations\WPML\Views\V2
 */

namespace Tribe\Events\Integrations\WPML\Views\V2;

/**
 * Class Filters
 *
 * @since   5.2.1
 *
 * @package Tribe\Events\Integrations\WPML\Views\V2
 */
class Filters {

	/**
	 * Translates the View URL.
	 *
	 * @since 5.2.1
	 *
	 * @param string $url The original View URL.
	 *
	 * @return string The translated View URL.
	 */
	public static function translate_view_url( $url ) {
		$lang = static::get_request_lang();
		if ( false === $lang ) {
			return $url;
		}

		return apply_filters( 'wpml_permalink', $url, $lang );
	}

	/**
	 * Returns the current request language, read from the request cookie.
	 *
	 * @since 5.2.1
	 *
	 * @return string|false Either the request language, e.g. `fr`, or `false` to indicate the language could not be
	 *                      parsed from the request context.
	 */
	protected static function get_request_lang() {
		return defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : false;
	}

	/**
	 * Translates the URls contained in the View template variables.
	 *
	 * @since 5.2.1
	 *
	 * @param array<string,mixed> $template_vars The original View template variables.
	 *
	 * @return array<string,mixed> The View template variables, with the URLs there contained translated, if required.
	 */
	public static function translate_template_vars_urls( $template_vars = [] ) {
		if ( ! is_array( $template_vars ) ) {
			return $template_vars;
		}

		$url_keys = array_filter(
			$template_vars,
			static function ( $value, $key ) {
				return strpos( $key, 'url' ) !== false && (bool) filter_var( $value, FILTER_VALIDATE_URL );
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( 0 === count( $url_keys ) ) {
			return $template_vars;
		}

		$lang = static::get_request_lang();

		if ( false === $lang ) {
			return $template_vars;
		}

		foreach ( $url_keys as $key => $value ) {
			$template_vars[ $key ] = apply_filters( 'wpml_permalink', $value, $lang );
		}

		return $template_vars;
	}

	/**
	 * Translates the URL of the public Views, the ones selectable in the Views selector.
	 *
	 * @since 5.2.1
	 *
	 * @param array<string,array<string,mixed>> $public_views The original data for the current public Views.
	 *
	 * @return array<string,array<string,mixed>> $public_views The modified data for the current public Views.
	 */
	public static function translate_public_views_urls( $public_views = [] ) {
		if ( ! is_array( $public_views ) ) {
			return $public_views;
		}

		$lang = static::get_request_lang();

		if ( false === $lang ) {
			return $public_views;
		}

		array_walk(
			$public_views,
			static function ( $view_data ) use ( $lang ) {
				if ( ! isset( $view_data->view_url ) ) {
					return;
				}
				$view_data->view_url = apply_filters( 'wpml_permalink', $view_data->view_url, $lang );
			}
		);

		return $public_views;
	}

	/**
	 * Updates the Views v2 request URI used to set up the `$_SERVER['REQUEST_URI']` in the `View::setup_the_loop`
	 * method to make sure it will point to the correct URL.
	 *
	 * @since 5.2.1
	 *
	 * @param string $request_uri The original request URI.
	 *
	 * @return string The corrected request URI.
	 */
	public static function translate_view_request_uri( $request_uri ) {
		if ( ! is_string( $request_uri ) ) {
			return $request_uri;
		}

		$lang = static::get_request_lang();

		if ( false === $lang ) {
			return $request_uri;
		}

		if ( static::using_subdir() && strpos( $request_uri, '/' . $lang ) !== 0 ) {
			$request_uri = '/' . $lang . $request_uri;
		}

		return $request_uri;
	}

	/**
	 * Returns whether the current WPML URL translation setting is the sub-directory one (e.g. `http://foo.bar/it`) or
	 * not.
	 *
	 * @since 5.2.1
	 *
	 * @return bool Whether the current WPML URL translation setting is the sub-directory one or not.
	 */
	protected static function using_subdir() {
		/** @var \SitePress $sitepress */
		global $sitepress;
		$lang_negotiation = (int) $sitepress->get_setting( 'language_negotiation_type' );

		return WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY === $lang_negotiation;
	}
}
