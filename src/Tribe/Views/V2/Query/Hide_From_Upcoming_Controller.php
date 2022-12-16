<?php

namespace Tribe\Events\Views\V2\Query;

use Tribe__Cache_Listener as Cache_Listener;

/**
 * Class Hide_From_Upcoming_Controller
 *
 * @since   6.0.6
 *
 * @package Tribe\Events\Views\V2\Query
 */
class Hide_From_Upcoming_Controller {

	/**
	 * Stores the name of the Tribe Cache for the upcoming ids that need to be hidden.
	 *
	 * @since 6.0.6
	 *
	 * @var string
	 */
	protected $timed_option_key = 'events_hide_from_upcoming_ids';

	/**
	 * Determine which are the posts are supposed to be hidden.
	 * Please be careful with this query below, it's currently an unbound query, that is why it only runs once a day.
	 *
	 * @since 6.0.6
	 *
	 * @return array
	 */
	public function get_hidden_post_ids(): array {
		$expiration_trigger = Cache_Listener::TRIGGER_SAVE_POST;
		$hidden_ids = tribe_cache()->get( $this->timed_option_key, $expiration_trigger, null );

		if ( null === $hidden_ids ) {
			global $wpdb;

			$sql = "
				SELECT {$wpdb->postmeta}.post_id
				FROM {$wpdb->postmeta}
				WHERE 1=1
				AND {$wpdb->postmeta}.meta_key = '_EventHideFromUpcoming'
				AND ( {$wpdb->postmeta}.meta_value = 'yes' OR {$wpdb->postmeta}.meta_value = '1' )
				GROUP BY {$wpdb->postmeta}.post_id;
			";
			$hidden_ids = $wpdb->get_col( $sql );

			tribe_cache()->set( $this->timed_option_key, $hidden_ids, DAY_IN_SECONDS, $expiration_trigger );
		}

		return (array) $hidden_ids;
	}

}