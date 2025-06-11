<?php
/**
 * A trait to group the methods common to all The Events Calendar blocks.
 *
 * @since 6.13.0
 */

namespace TEC\Events\Traits;

use Tribe__Events__Main as TEC;

/**
 * Trait Block_Trait.
 *
 * @since 6.13.0
 */
trait Block_Trait {

	/**
	 * Register the Assets for when this block is active
	 *
	 * @since 4.7
	 * @since 6.13.0 Moved to a trait to avoid code duplication.
	 *
	 * @return void
	 */
	public function assets() {
		if ( ! $this->should_register_assets() ) {
			return;
		}

		tec_asset(
			tribe( 'tec.main' ),
			"tribe-events-block-{$this->slug()}",
			"{$this->slug()}/frontend.css",
			[],
			'wp_enqueue_scripts',
			[
				'conditionals' => [ $this, 'has_block' ],
				'group_path'   => TEC::class . '-packages',
			]
		);
	}
}
