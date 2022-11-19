<?php

namespace Tribe\Events\Views\V2\Query;

/**
 * Class Hide_From_Upcoming_Controller
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */
class Hide_From_Upcoming_Controller {

	protected $timed_option_key = 'events_hide_from_upcoming_ids';

	public function get_hidden_post_ids(): array {
		$hidden_ids = tec_timed_option()->get( $this->timed_option_key );

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

			tec_timed_option()->set( $this->timed_option_key, $hidden_ids, DAY_IN_SECONDS );
		}

		return $hidden_ids;
	}

}