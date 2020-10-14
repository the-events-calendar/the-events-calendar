<?php
/**
 * Manages integration between WPML and Views v2 filters of The Events Calendar.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\WPML\Views\V2
 */

namespace Tribe\Events\Integrations\WPML\Views\V2;

/**
 * Class Filters
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\WPML\Views\V2
 */
class Filters {
	/**
	 * Translates the View URL.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
}
