<?php
/**
 * Primarily focused on helping users who established a site with 3.12.1 or
 * an even earlier release to migrate ECP custom meta/additional field data
 * to a new more readily searchable form.
 */
class Tribe__Events__Pro__Admin__Custom_Meta_Tools {
	/**
	 * Returns a list of event post IDs for those events believed to require an
	 * additional field update (ie, they have a single ECP additional field but
	 * not individual
	 *
	 * @param int    $limit  defaults to -1, representing "unlimited"
	 *
	 * @return array
	 */
	public function find_events_needing_update( $limit = -1 ) {
		$event_ids = array();

		foreach ( $this->multichoice_fields() as $custom_field ) {
			// Find any posts needing updates for a specific field type
			$event_ids += $this->find_events_needing_update_for( $custom_field[ 'name' ], $limit );
			$event_ids  = array_unique( $event_ids );

			// If we've reached our limit shorten the result set and bail out of the loop
			if ( $limit > 0 && count( $event_ids ) > $limit ) {
				array_splice( $event_ids, $limit );
				break;
			}
		}

		return $event_ids;
	}

	/**
	 * Returns a list of event post IDs that have the supplied custom field but
	 * only in it's "ordinary" form (ie, where multiple values are held in a single
	 * post meta record).
	 *
	 * @param string $field_name
	 * @param int    $limit       follows normal rules, ie -1 represents unlimited
	 *
	 * @return array
	 */
	public function find_events_needing_update_for( $field_name, $limit ) {
		global $wpdb;

		$limit = ( $limit > 0 )
			? ' LIMIT ' . absint( $limit ) . ' '
			: '';

		$query = $wpdb->prepare( "
				-- Find all post IDs associated with the specified legacy custom field key
				SELECT DISTINCT( post_id )
				FROM   $wpdb->postmeta
				WHERE  meta_key = %s

				-- Which have not yet been assigned to a new multichoice custom field key
				AND    post_id NOT IN (
						   SELECT DISTINCT( post_id )
						   FROM   $wpdb->postmeta
						   WHERE  meta_key = %s
					   )
				$limit
			", $field_name, "_$field_name"
		);

		return array_map( 'intval', (array) $wpdb->get_col( $query ) );
	}

	/**
	 * Rebuilds the (ECP) custom/additional field data for the specified event.
	 *
	 * @todo Fix! currently overwrites/wipes existing values
	 * @param $event_id
	 */
	public function rebuild_fields( $event_id ) {
		foreach ( $this->multichoice_fields() as $custom_field ) {
			// Break the field apart into its consituent elements: even if there is only
			// a single value, we still want to perform this step for multichoice fields
			$values = explode( '|', get_post_meta( $event_id, $custom_field['name'], true ) );

			// Trigger an update
			Tribe__Events__Pro__Custom_Meta::save_single_event_meta( $event_id, array(
				$custom_field['name'] = $values,
			) );
		}
	}

	/**
	 * Provides a list of all currently defined (ECP) custom fields which are "multichoice"
	 * in nature (for example, checkbox-type fields would be included in this list by default).
	 *
	 * @return array
	 */
	public function multichoice_fields() {
		$multichoice_fields = array();
		$defined_fields     = (array) tribe_get_option( 'custom-fields', array() );

		foreach ( $defined_fields as $custom_field ) {
			if ( Tribe__Events__Pro__Custom_Meta::is_multichoice( $custom_field[ 'type' ] ) ) {
				$multichoice_fields[] = $custom_field;
			}
		}

		return $multichoice_fields;
	}
}