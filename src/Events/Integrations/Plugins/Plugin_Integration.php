<?php

namespace TEC\Events\Integrations\Plugins;

trait Plugin_Integration {
	/**
	 * Gets the integration type.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_type(): string {
		return 'plugin';
	}
}