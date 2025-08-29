<?php
/**
 * Class Tribe__Events__Integrations__ACF__ACF
 *
 * Handles the integration between The Events Calendar plugin and Advanced Custom Fields.
 *
 * This class is meant to be an entry point hooking specialized classes and not
 * a logic hub per se.
 */
class Tribe__Events__Integrations__ACF__ACF {

	/**
	 * @var Tribe__Events__Integrations__ACF__ACF
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__ACF__ACF
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hooks the filters and actions needed for this integration to work.
	 *
	 * @since 4.6.3
	 */
	public function hook() {
		// Register the ACF compatibility script with conditional loading.
		tec_asset(
			Tribe__Events__Main::instance(),
			'tribe-admin-acf-compat',
			'tribe-admin-acf-compat.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'should_enqueue_acf_compat' ],
			]
		);
	}

	/**
	 * Determines if the ACF compatibility script should be enqueued.
	 *
	 * @since 4.6.3
	 *
	 * @return bool Whether the script should be enqueued.
	 */
	public function should_enqueue_acf_compat() {
		$admin_helpers = Tribe__Admin__Helpers::instance();
		return $admin_helpers->is_post_type_screen();
	}
}
