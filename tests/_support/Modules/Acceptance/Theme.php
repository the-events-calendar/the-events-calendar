<?php

namespace Tribe\Tests\Modules\Pro\Acceptance;

class Theme extends \Codeception\Module {

	public function getDefaultThemeSlug() {
		return 'twentyfifteen';
	}

	public function getThemeSidebars() {
		return [
			'sidebar-1'
		];
	}
}