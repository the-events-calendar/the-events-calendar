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
}
