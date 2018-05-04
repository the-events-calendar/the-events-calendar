<?php

class Tribe__Events__Aggregator__Processes__Import_Events extends Tribe__Process__Queue {
	/**
	 * @var string
	 */
	protected $transitional_id;

	/**
	 * @var bool Whether the current item has dependencies or not.
	 */
	protected $has_dependencies = true;

	/**
	 * Returns the async process action name.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function action() {
		return 'ea_import_events';
	}

	public function add_transitional_data( array $event ) {
		$venue_id      = Tribe__Utils__Array::get( $event, 'EventVenueID', false );
		$organizer_ids = Tribe__Utils__Array::get( $event, array( 'Organizer', 'OrganizerID' ), false );

		if ( false !== $venue_id ) {
			update_post_meta( $venue_id, $this->get_transitional_meta_key(), get_post_meta( $venue_id, '_tribe_aggregator_global_id', true ) );
		}

		if ( false !== $organizer_ids && is_array( $organizer_ids ) ) {
			foreach ( $organizer_ids as $organizer_id ) {
				update_post_meta( $organizer_id, $this->get_transitional_meta_key(), get_post_meta( $organizer_id, '_tribe_aggregator_global_id', true ) );
			}
		}
	}

	public function get_transitional_meta_key( $transitional_id = null ) {
		if ( null === $transitional_id ) {
			$transitional_id = $this->transitional_id;
		}

		return '_tribe_import_' . $transitional_id;
	}

	/**
	 * @param string $transitional_id
	 */
	public function set_transitional_id( $transitional_id ) {
		$this->transitional_id = $transitional_id;
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$record_id             = $item['record_id'];
		$data                  = (array) $item['data'];
		$this->transitional_id = filter_var( $item['transitional_id'], FILTER_SANITIZE_STRING );
		// @todo use the user
		$user_id = $item['user_id'];

		$dependencies = $this->parse_linked_post_dependencies( $data );

		if ( empty( $dependencies ) ) {
			$this->has_dependencies = false;
			$result = $this->insert_event( $record_id, (object) $data );

			return $this->doing_sync ? $result : false;
		}

		$dependencies_ids = $this->check_dependencies( $dependencies );

		if ( $dependencies_ids ) {
			$this->set_linked_posts_ids( $data, $dependencies_ids );
			$result = $this->insert_event( $record_id, (object) $data );

			return $this->doing_sync ? $result : false;
		}

		return $item;
	}

	/**
	 * Parses the Event Venue and Organizer dependencies.
	 *
	 * @since TBD
	 *
	 * @param array $data
	 *
	 * @return array An array containing a list of identifiers (contextual to the import) for the
	 *               dependencies.
	 */
	protected function parse_linked_post_dependencies( $data ) {
		$dependencies = array();

		if ( ! empty( $data['depends_on'] ) ) {
			$dependencies = array_values( (array) $data['depends_on'] );
		}

		return $dependencies;
	}

	/**
	 * Inserts an event.
	 *
	 * @since TBD
	 *
	 * @param int $record_id
	 * @param array $data
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	protected function insert_event( $record_id, $data ) {
		$record = $this->get_record( $record_id );

		if ( ! $this->has_dependencies ) {
			add_action( 'tribe_aggregator_after_insert_post', array( $this, 'add_transitional_data' ) );
		}

		return $record->insert_posts( array( $data ) );
	}

	/**
	 * @param $record_id
	 *
	 * @return null|Tribe__Error|Tribe__Events__Aggregator__Record__Abstract
	 */
	protected function get_record( $record_id ) {
		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );
		$record  = $records->get_by_post_id( $record_id );

		return $record;
	}

	/**
	 * Checks the database to make sure all the dependencies are available.
	 *
	 * @since TBD
	 *
	 * @param $dependencies
	 *
	 * @return array|bool e
	 */
	protected function check_dependencies( $dependencies ) {
		if ( empty( $dependencies ) ) {
			return true;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$meta_values = array();
		foreach ( $dependencies as $meta_value ) {
			$meta_values[] = $wpdb->prepare( '%s', $meta_value );
		}
		$meta_values = implode( ',', $meta_values );

		$query = $wpdb->prepare(
			"SELECT pm.post_id, p.post_type
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p
			ON p.ID = pm.post_id
			WHERE meta_key = %s 
			AND meta_value IN ({$meta_values})",
			$this->get_transitional_meta_key()
		);

		$ids = $wpdb->get_results( $query );

		$can_create = count( $ids ) === count( $dependencies );

		return $can_create ? $ids : false;
	}

	protected function set_linked_posts_ids( &$data, array $dependencies_ids ) {
		$linked_post_types = array(
			'venue'     => Tribe__Events__Venue::POSTTYPE,
			'organizer' => Tribe__Events__Organizer::POSTTYPE,
		);

		foreach ( $linked_post_types as $linked_post_key => $linked_post_type ) {
			$linked_post_ids = wp_list_pluck( wp_list_filter( $dependencies_ids, array( 'post_type' => $linked_post_type ) ), 'post_id' );

			if ( empty( $linked_post_ids ) ) {
				continue;
			}

			if ( 'venue' === $linked_post_key ) {
				$data['venue'] = (object) array( '_venue_id' => reset($linked_post_ids) );
			} else {
				$data[ $linked_post_key ] = array();
				foreach ( $linked_post_ids as $id ) {
					$data[ $linked_post_key ][] = (object) array( "_{$linked_post_key}_id" => $id );
				}
			}
		}

		return $data;
	}

	protected function complete() {
		parent::complete();

		/** @var wpdb $wpdb */
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
				$this->get_transitional_meta_key()
			)
		);
	}
}