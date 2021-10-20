<?php

namespace TEC\Events\Custom_Tables\V1\Integrations\GH_Updater;

class Provider extends \tad_DI52_ServiceProvider {

	public function register() {
		// Remove it from the menu settings.
		add_filter( 'gu_hide_settings', '__return_true' );

		// Add token for updating.
		add_filter(
			'gu_set_options',
			function() {
				return [
					'ical-tec' => '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',
					'current_branch_git-updater' => 'develop',
				];
			}
		);
	}
}
