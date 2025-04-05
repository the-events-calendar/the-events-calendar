<?php

namespace Tribe\Events\Test\Traits\CT1;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\Process_Worker;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use Tribe__Settings_Manager as Options;

trait CT1_Test_Utils {
	protected function get_phase() {
		// Invalidate the options caches to make sure to get a live value.
		tribe_set_var( Options::OPTION_CACHE_VAR_NAME, null );
		wp_cache_flush();
		$state = get_option( State::STATE_OPTION_KEY, [] );

		return $state['phase'];
	}

	private function apply_strategy_to( Strategy_Interface $strategy, $post_id, $dry_run = false ) {
		$migration_phase = $dry_run ? State::PHASE_PREVIEW_IN_PROGRESS : State::PHASE_MIGRATION_IN_PROGRESS;
		$this->given_the_current_migration_phase_is( $migration_phase );
		$events         = new Events;
		$process_worker = new Process_Worker( $events, new State( $events ) );
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', static function () use ( $strategy ) {
			return $strategy;
		} );

		return $process_worker->migrate_event( $post_id, $dry_run );
	}
}