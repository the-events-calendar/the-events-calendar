<?php

namespace TEC\Events\Integrations\Plugins;

trait Plugin_Integration {
	/**
	 * Gets the integration type.
	 *
	 * @since 6.0.4
	 *
	 * @return string
	 */
	public static function get_type(): string {
		return 'plugin';
	}
}
