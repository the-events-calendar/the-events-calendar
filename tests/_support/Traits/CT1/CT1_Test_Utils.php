<?php

namespace Tribe\Events\Test\Traits\CT1;

use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Settings_Manager as Options;

trait CT1_Test_Utils {
	protected function get_phase() {
		// Invalidate the options caches to make sure to get a live value.
		tribe_set_var( Options::OPTION_CACHE_VAR_NAME, null );
		wp_cache_flush();
		$state = tribe_get_option( State::STATE_OPTION_KEY, [] );

		return $state['phase'];
	}
}