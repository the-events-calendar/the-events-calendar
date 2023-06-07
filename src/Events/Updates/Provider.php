<?php

namespace TEC\Events\Updates;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use Tribe__Main;

class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * Registers hooks for TEC Updates.
	 *
	 * @since TBD
	 */
	public function register() {
		add_action( 'update_option_' . Tribe__Main::OPTIONNAME, [ $this, 'on_cutoff_change_fix_all_day_events' ], 10, 2 );
		add_action( 'tec_events_sync_utc_dates', [ $this, 'async_sync_utc_dates' ], 10, 2 );
	}

	/**
	 * Removes hooks.
	 */
	public function unregister() {
		remove_action( 'update_option_' . Tribe__Main::OPTIONNAME, [ $this, 'on_cutoff_change_fix_all_day_events' ], 10 );
		remove_action( 'tec_events_sync_utc_dates', [ $this, 'async_sync_utc_dates' ] );
	}

	/**
	 * Updates the start/end time on all day events to match the EOD cutoff
	 *
	 * @since TBD
	 */
	public function on_cutoff_change_fix_all_day_events( $old_value, $new_value ) {
		$this->container->make( Sync_UTC::class )->on_cutoff_change_fix_all_day_events( $old_value, $new_value );
	}

	/**
	 * Will recurse and send an async call to update a batch of events based on the initial repository args.
	 *
	 * @since TBD
	 *
	 * @param array $repository_args The repository search args for the events to fetch for UTC sync.
	 * @param int   $iteration       This is used as a way to track how many times we recurse and exit out.
	 */
	public function async_sync_utc_dates( array $repository_args, $iteration = 0 ) {
		$this->container->make( Sync_UTC::class )->async_sync_utc_dates( $repository_args, $iteration );
	}
}