<?php

namespace TEC\Events\Integrations\Plugins\TEC_Tweaks_Extension;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Plugin_Integration;
use Tribe__Events__Main;

/**
 * Class Provider
 *
 * @since 6.4.1
 *
 * @package TEC\Events\Integrations\Plugins\TEC_Tweaks_Extension
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'tribe-ext-tec-tweaks';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return class_exists( \Tribe\Extensions\Tec_Tweaks\Main::class, false );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		$this->remove_end_time_extension_settings();
	}

	/**
	 * This handles removing the end time extension settings from the tweaks extension.
	 */
	public function remove_end_time_extension_settings() {
		add_filter( 'tribe_get_option_tribe_ext_tec_tweaks_remove_event_end_time', '__return_empty_array' );
		add_filter(
			'tec_general_settings_viewing_section',
			static function ( $fields, $id ) {
				if ( $id !== 'tec-tweaks' ) {
					return $fields;
				}
				unset( $fields['tec_labs_tec_tweaks_remove_event_end_time'] );

				return $fields;
			},
			10,
			2
		);
	}
}
