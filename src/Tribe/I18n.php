<?php
/**
 * Handles internationalization of The Events Calendar strings.
 *
 * @since   5.1.1
 *
 * @package Tribe\Events
 */

namespace Tribe\Events;

use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Events__Main as TEC;
use Tribe__Main as Common;

/**
 * Class I18n
 *
 * @since   5.1.1
 *
 * @package Tribe\Events
 */
class I18n {
	/**
	 * A flag to require translation compilation of the input as is.
	 *
	 * @since 5.1.5
	 */
	const COMPILE_INPUT = 1;

	/**
	 * A flag to require translation compilation of the lower-case version of the input.
	 *
	 * @since 5.1.5
	 */
	const COMPILE_STRTOLOWER = 2;

	/**
	 * A flag to require translation compilation of the input in its title form.
	 *
	 * @since 5.1.5
	 */
	const COMPILE_UCFIRST = 4;

	/**
	 * A flag to require translation compilation of the input in all the available forms.
	 *
	 * @since 5.1.5
	 */
	const COMPILE_ALL = 7;

	/**
	 * An instance of the The Events Calendar main class.
	 *
	 * @since 5.1.1
	 *
	 * @var TEC
	 */
	protected $tec;

	/**
	 * I18n constructor.
	 *
	 * @param TEC $tec An instance of The Events Calendar main class.
	 */
	public function __construct( TEC $tec ) {
		$this->tec = $tec;
	}

	/**
	 * Get all possible translations for a String based on the given Languages and Domains
	 *
	 * WARNING: This function is slow because it deals with files, so don't overuse it!
	 *
	 * @since 5.1.1 Moved here from Tribe__Events__Main.
	 * @since 5.1.5   Add support for the $flags argument.
	 *
	 * @param array  $strings          An array of strings (required).
	 * @param array  $languages        Which l10n to fetch the string (required).
	 * @param array  $domains          Possible Domains to re-load.
	 * @param string $default_language The default language to avoid re-doing that.
	 * @param int    $flags            An integer resulting from the combination of compilation flags;
	 *                                 defaults to `static::COMPILE_ALL` to compile all versions of the translations.
	 *                                 `static::COMPILE_INPUT` will compile the translation for the string, as input.
	 *                                 `static::COMPILE_STRTOLOWER` will compile the translation for the string in its
	 *                                 lowercase version.
	 *                                 `static::COMPILE_UCFIRST` will compile the translation for the string in its
	 *                                 title version.
	 *
	 * @return array<array<string>> A multi level array with the possible translations for the given strings
	 */
	public function get_i18n_strings( $strings, $languages, $domains = [], $default_language = 'en_US', $flags = 7 ) {
		$domains = wp_parse_args(
			$domains,
			[
				// The `default` domain doesn't need file path.
				'default'             => true,
				'the-events-calendar' => $this->tec->plugin_dir . 'lang/',
			]
		);

		return $this->get_i18n_strings_for_domains( $strings, $languages, $domains, $flags );
	}

	/**
	 * Get all possible translations for a String based on the given Languages and Domains.
	 *
	 * WARNING: This function is slow because it deals with files, so don't overuse it!
	 * Differently from the `get_i18n_strings` method this will not use any domain that's not specified.
	 *
	 * @since 5.1.1
	 * @since 5.1.5   Add support for the $flags argument.
	 *
	 * @param array $strings    An array of strings (required).
	 * @param array $languages Which l10n to fetch the string (required).
	 * @param array $domains   Possible domains to re-load.
	 * @param int   $flags     An integer resulting from the combination of compilation flags;
	 *                         defaults to `static::COMPILE_ALL` to compile all versions of the translations.
	 *                         `static::COMPILE_INPUT` will compile the translation for the string, as input.
	 *                         `static::COMPILE_STRTOLOWER` will compile the translation for the string in its lowercase
	 *                         version.
	 *                         `static::COMPILE_UCFIRST` will compile the translation for the string in its title
	 *                         version.
	 *
	 * @return array<string,array|string> A multi level array with the possible translations for the given strings.
	 *
	 * @todo Include support for the `load_theme_textdomain` + `load_muplugin_textdomain`
	 */
	public function get_i18n_strings_for_domains( $strings, $languages, $domains = [ 'default' ], $flags = 7 ) {
		sort( $languages );
		$strings_buffer = [ $strings ];

		foreach ( $languages as $language ) {
			// Override the current locale w/ the one we need to compile the translations.
			$language_strings = $this->with_locale(
				$language,
				[ $this, 'compile_translations' ],
				[ $strings, $domains, $flags ]
			);
			$strings_buffer[] = $language_strings;
		}

		$strings = count( $strings_buffer ) > 1
			? array_merge_recursive( ... $strings_buffer )
			: reset( $strings_buffer );

		// Prevent empty strings and duplicates.
		foreach ( $strings as $key => $value ) {
			$strings[ $key ] = array_filter(
				array_unique(
					array_map( 'sanitize_key', (array) $value )
				)
			);
		}

		return $strings;
	}

	/**
	 * Get all possible translations for a URL String based on the given Languages and Domains.
	 *
	 * WARNING: This function is slow because it deals with files, so don't overuse it!
	 * Differently from the `get_i18n_strings` method this will not use any domain that's not specified.
	 *
	 * This function is same as above one, but instead of sanitizing with 'sanitize_key()' which removes '%',
	 * it uses 'sanitize_title()'.
	 *
	 * @since 6.0.2
	 *
	 * @param array<string> $strings   An array of strings (required).
	 * @param array<string> $languages Which l10n to fetch the string (required).
	 * @param array<string> $domains   Possible domains to re-load.
	 * @param int           $flags     An integer resulting from the combination of compilation flags;
	 *                                 defaults to `static::COMPILE_ALL` to compile all versions of the translations.
	 *                                 `static::COMPILE_INPUT` will compile the translation for the string, as input.
	 *                                 `static::COMPILE_STRTOLOWER` will compile the translation for the string in its
	 *                                 lowercase version.
	 *                                 `static::COMPILE_UCFIRST` will compile the translation for the string in its title
	 *                                 version.
	 *
	 * @return array<string,array|string> A multi level array with the possible translations for the given strings.
	 */
	public function get_i18n_url_strings_for_domains( $strings, $languages, $domains = [ 'default' ], $flags = 7 ) {
		sort( $languages );
		$strings_buffer = [ $strings ];

		foreach ( $languages as $language ) {
			// Override the current locale w/ the one we need to compile the translations.
			$language_strings = $this->with_locale(
				$language,
				[ $this, 'compile_translations' ],
				[ $strings, $domains, $flags ]
			);
			$strings_buffer[] = $language_strings;
		}

		$strings = count( $strings_buffer ) > 1
			? array_merge_recursive( ... $strings_buffer )
			: reset( $strings_buffer );

		// Prevent empty strings and duplicates.
		foreach ( $strings as $key => $value ) {
			$strings[ $key ] = array_filter(
				array_unique(
					array_map( 'sanitize_title', (array) $value )
				)
			);
		}

		return $strings;
	}

	/**
	 * Executes a callback ensuring the `current_locale` will be set to the specified language code.
	 *
	 * The method will backup and detach the functions and methods currently filtering the `locale` filter to execute
	 * the callback in isolation and restore the filters after that.
	 * The main purpose of this method is to avoid a rat race against plugins and themes that will filter the locale
	 * by attaching the filtering method or function at `PHP_INT_MAX`.
	 *
	 * @since 5.1.1
	 * @since 5.4.0 Changed the method visibility to public.
	 *
	 * @param string       $locale The locale to set for the execution of the callback.
	 * @param callable     $do     The callable to execute in the context of a specific locale.
	 * @param array<mixed> $args   A set of arguments that will be passed to the callback.
	 *
	 * @return mixed The callback return value, if any.
	 */
	public function with_locale( $locale, callable $do, array $args = [] ) {
		global $wp_filter;
		// Backup the current state of the locale filter.
		$locale_filters_backup = isset( $wp_filter['locale'] ) ? $wp_filter['locale'] : new \WP_Hook;
		// Set the `locale` filter to a new hook, nothing is hooked to it.
		$wp_filter['locale'] = new \WP_Hook();

		$force_locale = static function () use ( $locale ) {
			return $locale;
		};

		add_filter( 'locale', $force_locale );
		$result = $do( ...$args );
		remove_filter( 'locale', $force_locale );

		$domains = isset( $args[1] ) ? (array) $args[1] : false;
		if ( false !== $domains ) {
			foreach ( $domains as $domain => $file ) {
				// Reload it with the correct language.
				unload_textdomain( $domain );

				if ( 'default' === $domain ) {
					load_default_textdomain();
				} elseif ( is_string( $file ) ) {
					Common::instance()->load_text_domain( $domain, $file );
				}
			}
		}

		// Restore the `locale` filtering functions.
		$wp_filter['locale'] = $locale_filters_backup;

		return $result;
	}

	/**
	 * Compiles the translations for a set of strings iterating on a set of domains.
	 *
	 * The 4th argument is a bitmask to control the compiled translations.
	 * E.g. `$i18n->compile_translations( $strings, $domains, I18n::COMPILE_STRTOLOWER);` will only compile
	 * translations of the strings in their `strtolower` versions.
	 * Combine the flags using the usual PHP syntax: `I18n::COMPILE_INPUT | I18n::COMPILE_STRTOLOWER` to compile
	 * only the translation of the string as input and in their lowercase version.
	 *
	 * @since 5.1.1
	 * @since 5.1.5   Add support for the $flags argument.
	 *
	 * @param array<string,array|string> $strings The set of strings to compile the translations for.
	 * @param string|array<string>       $domains The domain(s) that should be used to compile the string translations.
	 * @param int                        $flags   An integer resulting from the combination of compilation flags;
	 *                                            defaults to `static::COMPILE_ALL` to compile all versions of the
	 *                                            translations.
	 *                                            `static::COMPILE_INPUT` will compile the translation for the string,
	 *                                            as input.
	 *                                            `static::COMPILE_STRTOLOWER` will compile the translation for the
	 *                                            string in its lowercase version.
	 *                                            `static::COMPILE_UCFIRST` will compile the translation for the string
	 *                                            in its title version.
	 *
	 * @return array<string|array> A map of the compiled string translations.
	 */
	public function compile_translations( array $strings, $domains, $flags = 7 ) {
		$cache_salts = [ $strings, $domains, get_locale() ];
		$cache_key   = __METHOD__ . md5( serialize( $cache_salts ) );

		$expiration_trigger = Cache_Listener::TRIGGER_UPDATED_OPTION;
		$cached             = tribe_cache()->get( $cache_key, $expiration_trigger, false, DAY_IN_SECONDS );

		if ( false !== $cached ) {
			return $cached;
		}

		foreach ( (array) $domains as $domain => $file ) {
			// Reload it with the correct language.
			unload_textdomain( $domain );

			if ( 'default' === $domain ) {
				load_default_textdomain();
			} else {
				Common::instance()->load_text_domain( $domain, $file );
			}

			// Loop on the strings the build the possible translations.
			foreach ( $strings as $key => $value ) {
				$value = is_array( $value ) ? reset( $value ) : $value;
				if ( ! is_string( $value ) ) {
					continue;
				}

				// Make sure we have an array.
				$strings[ $key ] = (array) $strings[ $key ];

				// Grab the possible strings for default and any other domain.
				if ( 'default' === $domain ) {
					if ( $flags & static::COMPILE_INPUT ) {
						$strings[ $key ][] = __( $value );
					}
					if ( $flags & static::COMPILE_STRTOLOWER ) {
						$strings[ $key ][] = __( strtolower( $value ) );
					}
					if ( $flags & static::COMPILE_UCFIRST ) {
						$strings[ $key ][] = __( ucfirst( $value ) );
					}
				} else {
					if ( $flags & static::COMPILE_INPUT ) {
						$strings[ $key ][] = __( $value, $domain );
					}
					if ( $flags & static::COMPILE_STRTOLOWER ) {
						$strings[ $key ][] = __( strtolower( $value ), $domain );
					}
					if ( $flags & static::COMPILE_UCFIRST ) {
						$strings[ $key ][] = __( ucfirst( $value ), $domain );
					}
				}
			}
		}

		tribe_cache()->set( $cache_key, $strings, DAY_IN_SECONDS, $expiration_trigger );

		return $strings;
	}
}
