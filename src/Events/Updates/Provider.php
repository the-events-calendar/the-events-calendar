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
	}

	/**
	 * Removes hooks.
	 */
	public function unregister() {
		remove_action( 'update_option_' . Tribe__Main::OPTIONNAME, [ $this, 'on_cutoff_change_fix_all_day_events' ], 10 );
	}

	/**
	 * Updates the start/end time on all day events to match the EOD cutoff
	 *
	 * @since TBD
	 */
	public function on_cutoff_change_fix_all_day_events( $old_value, $new_value ) {
		$this->container->make( Sync_All_Day_Dates::class )->on_cutoff_change_fix_all_day_events( $old_value, $new_value );
	}
}
