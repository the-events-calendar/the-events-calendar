<?php

namespace Tribe\Events;

use Tribe__Events__Main as TEC;

class I18nTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should correctly set locale in the with_locale method
	 *
	 * @test
	 */
	public function should_correctly_set_locale_in_the_with_locale_method() {
		$tec  = TEC::instance();
		$i18n = new I18n( $tec );

		$i18n->with_locale( 'fr_FR', static function () use ( &$filtered_locale ) {
			$filtered_locale = get_locale();
		} );

		$this->assertEquals( 'fr_FR', $filtered_locale );
	}

	/**
	 * It should correctly set locale in with_locale method when hooked functions exist
	 *
	 * @test
	 */
	public function should_correctly_set_locale_in_with_locale_method_when_hooked_functions_exist() {
		$tec  = TEC::instance();
		$i18n = new I18n( $tec );
		add_filter( 'locale', static function () {
			return 'it_IT';
		} );

		$i18n->with_locale( 'fr_FR', static function () use ( &$filtered_locale ) {
			$filtered_locale = get_locale();
		} );

		$this->assertEquals( 'fr_FR', $filtered_locale );
	}

	/**
	 * It should correctly set locale in with_locale method when nothing is hooked to locale filter
	 *
	 * @test
	 */
	public function should_correctly_set_locale_in_with_locale_method_when_nothing_is_hooked_to_locale_filter() {
		$tec  = TEC::instance();
		$i18n = new I18n( $tec );
		global $wp_filter;
		unset( $wp_filter['locale'] );

		$i18n->with_locale( 'fr_FR', static function () use ( &$filtered_locale ) {
			$filtered_locale = get_locale();
		} );

		$this->assertEquals( 'fr_FR', $filtered_locale );
	}

	/**
	 * It should allow getting I18n strings for domain by language
	 *
	 * @test
	 */
	public function should_allow_getting_i_18_n_strings_for_domain_by_language(): void {
		// Translation files will not be loaded, fake them.
		$translations_map = [
			'en_US' => [
				'default'             => [
					'page' => 'page',
				],
				'the-events-calendar' => [
					'list'  => 'list',
					'page'  => 'page',
					'month' => 'month'
				],
			],
			'it_IT' => [
				'default'             => [
					'page' => 'pagina',
				],
				'the-events-calendar' => [
					'list'  => 'lista',
					'page'  => 'pagina',
					'month' => 'mese'
				],
			],
			'fr_FR' => [
				'default'             => [
					'page' => 'page',
				],
				'the-events-calendar' => [
					'list'  => 'liste',
					'page'  => 'page',
					'month' => 'mois'
				],
			],
		];
		add_filter( 'gettext', function ( string $translation, string $text, string $domain ) use ( $translations_map ) {
			$locale = get_locale();

			return $translations_map[ $locale ][ $domain ][ $text ] ?? $translation;
		}, 10, 3 );
		$tec  = TEC::instance();
		$i18n = new I18n( $tec );

		$strings_by_language = $i18n->get_i18n_strings_for_domains( [
			'list',
			'page',
			'month',
		],
			[ 'en_US', 'it_IT', 'fr_FR' ],
			[ 'default' => __DIR__, 'the-events-calendar' => __DIR__ ], // Not loading translation files.
			I18n::RETURN_BY_LANGUAGE | I18n::COMPILE_INPUT
		);

		$expected = [
			0       =>
				[
					[ 'list', ],
					[ 'page', ],
					[ 'month', ],
				],
			'en_US' =>
				[
					[ 'list', ],
					[ 'page', ],
					[ 'month', ],
				],
			'fr_FR' =>
				[
					[ 'list', 'liste', ],
					[ 'page', ],
					[ 'month', 'mois', ],
				],
			'it_IT' =>
				[
					[ 'list', 'lista', ],
					[ 'page', 'pagina', ],
					[ 'month', 'mese', ],
				],
		];
		$this->assertEquals( $expected, $strings_by_language );
	}

	/**
	 * It should allow compiling strings including slug
	 *
	 * @test
	 */
	public function should_allow_compiling_strings_including_slug(): void {
		// Translation files will not be loaded, fake them.
		$translations_map = [
			'en_US' => [
				'default'             => [
					'page' => 'page',
				],
				'the-events-calendar' => [
					'list'  => 'list',
					'page'  => 'page',
					'today' => 'today'
				],
			],
			'it_IT' => [
				'default'             => [
					'page' => 'pagina',
				],
				'the-events-calendar' => [
					'list'  => 'lista',
					'page'  => 'pagina',
					'today' => 'giorno'
				],
			],
			'fr_FR' => [
				'default'             => [
					'page' => 'page',
				],
				'the-events-calendar' => [
					'list'  => 'liste',
					'page'  => 'page',
					'today' => "auhjourd'hui"
				],
			],
		];
		add_filter( 'gettext', function ( string $translation, string $text, string $domain ) use ( $translations_map ) {
			$locale = get_locale();

			return $translations_map[ $locale ][ $domain ][ $text ] ?? $translation;
		}, 10, 3 );
		$tec  = TEC::instance();
		$i18n = new I18n( $tec );

		$strings_by_language = $i18n->get_i18n_strings_for_domains( [
			'list',
			'page',
			'today',
		],
			[ 'en_US', 'it_IT', 'fr_FR' ],
			[ 'default' => __DIR__, 'the-events-calendar' => __DIR__ ], // Not loading translation files.
			I18n::RETURN_BY_LANGUAGE | I18n::COMPILE_INPUT | I18n::COMPILE_SLUG
		);

		$expected = [
			0       =>
				[
					[ 'list', ],
					[ 'page', ],
					[ 'today', ],
				],
			'en_US' =>
				[
					[ 'list', ],
					[ 'page', ],
					[ 'today', ],
				],
			'fr_FR' =>
				[
					[ 'list', 'liste', ],
					[ 'page', ],
					[ 'today', 'auhjourdhui', "auhjourd'hui" ], // Order matters to match the apostrophe version.
				],
			'it_IT' =>
				[
					[ 'list', 'lista', ],
					[ 'page', 'pagina', ],
					[ 'today', 'giorno', ],
				],
		];
		$this->assertEquals( $expected, $strings_by_language );
	}
}
