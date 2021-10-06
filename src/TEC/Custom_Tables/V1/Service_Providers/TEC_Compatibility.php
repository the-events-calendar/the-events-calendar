<?php
/**
 * Registers the sub-set of functionality provided by the plugin that integrates it with The Events Calendar.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Service_Providers
 */

namespace TEC\Custom_Tables\V1\Service_Providers;

use TEC\Custom_Tables\V1\Views\V2;
use Tribe__Events__Admin_List;
use Tribe__Events__Main as TEC;

/**
 * Class TEC_Compatibility
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Service_Providers
 */
class TEC_Compatibility extends \tad_DI52_ServiceProvider {

	/**
	 * Registers an action to look around when the plugins are loaded to load the
	 * ORM integration if possible.
	 *
	 * @since TBD
	 *
	 * @param $pro
	 */
	public function register() {
		if ( ! function_exists( 'tribe_events' ) ) {
			return;
		}

		if ( function_exists( 'tribe_events_views_v2_is_enabled' ) && tribe_events_views_v2_is_enabled() ) {
			$this->container->register( V2\Provider::class );
		}

		if ( tribe( 'context' )->doing_ajax( true ) ) {
			add_action( 'admin_init', [ $this, 'remove_admin_filters' ] );
		} else {
			add_action( 'current_screen', [ $this, 'remove_admin_filters' ] );
		}
	}

	/**
	 * Remove admin filters from TEC.
	 *
	 * @since TBD
	 */
	public function remove_admin_filters() {
		remove_filter( 'views_edit-' . TEC::POSTTYPE, [ Tribe__Events__Admin_List::class, 'update_event_counts' ] );
		remove_action( 'manage_posts_custom_column', [ Tribe__Events__Admin_List::class, 'custom_columns' ] );
	}
}
