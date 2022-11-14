<?php

namespace TEC\Events\Integrations\Themes;

trait Theme_Integration {
	/**
	 * Gets the integration type.
	 *
	 * @since 6.0.4
	 *
	 * @return string
	 */
	public static function get_type(): string {
		return 'theme';
	}
}
