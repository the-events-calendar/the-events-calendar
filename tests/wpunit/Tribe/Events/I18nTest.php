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
					[ 0 => 'list', ],
					[ 0 => 'page', ],
					[ 0 => 'month', ],
				],
			'en_US' =>
				[
					[ 0 => 'list', ],
					[ 0 => 'page', ],
					[ 0 => 'month', ],
				],
			'fr_FR' =>
				[
					[ 0 => 'list', 2 => 'liste', ],
					[ 0 => 'page', ],
					[ 0 => 'month', 2 => 'mois', ],
				],
			'it_IT' =>
				[
					[ 0 => 'list', 2 => 'lista', ],
					[ 0 => 'page', 1 => 'pagina', ],
					[ 0 => 'month', 2 => 'mese', ],
				],
		];
		$this->assertEquals( $expected, $strings_by_language );
	}
}
