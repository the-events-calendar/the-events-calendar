<?php
/**
 * A trait to group the methods common to all The Events Calendar blocks.
 *
 * @since TBD
 */

/**
 * Trait Tribe__Events__Editor__Blocks__Block_Trait.
 *
 * @since TBD
 */
trait Tribe__Events__Editor__Blocks__Block_Trait {

	/**
	 * Register the Assets for when this block is active
	 *
	 * @since 4.7
	 * @since TBD Moved to a trait to avoid code duplication.
	 *
	 * @return void
	 */
	public function assets() {
		tec_asset(
			tribe( 'tec.main' ),
			'tribe-events-block-' . $this->slug(),
			'app/' . $this->slug() . '/frontend.css',
			[],
			'wp_enqueue_scripts',
			[
				'conditionals' => [ $this, 'has_block' ],
			]
		);
	}
}
