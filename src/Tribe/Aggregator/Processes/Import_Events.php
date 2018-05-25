<?php

/**
 * Class Tribe__Events__Aggregator__Processes__Import_Events
 *
 * Imports events in an async queue.
 *
 * @since 4.6.16
 */
class Tribe__Events__Aggregator__Processes__Import_Events extends Tribe__Process__Queue {
	/**
	 * @var string
	 */
	protected $transitional_id;

	/**
	 * @var int The post ID of the record associated to this queue instance.
	 */
	protected $record_id;

	/**
	 * @var bool Whether the current item has dependencies or not.
	 */
	protected $has_dependencies = true;

	/**
	 * @var Tribe__Events__Aggregator__Record__Activity[]
	 */
	protected $activities = array();

	/**
	 * Returns the async process action name.
	 *
	 * @since 4.6.16
	 *
	 * @return string
	 */
	public static function action() {
		return 'ea_import_events';
	}

	/**
	 * Adds transitional data, used to check dependencies, to an event linked posts.
	 *
	 * @since 4.6.16
	 *
	 * @param array $event
	 */
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

	/**
	 * Returns the `meta_key` that will be used to store the transitional data
	 * in linked post for this import process.
	 *
	 * @since 4.6.16
	 *
	 * @param null $transitional_id
	 *
	 * @return string
	 */
	public function get_transitional_meta_key( $transitional_id = null ) {
		if ( null === $transitional_id ) {
			$transitional_id = $this->transitional_id;
		}

		return '_tribe_import_' . $transitional_id;
	}

	/**
	 * Sets the final part `meta_key` that should be used to store transitional
	 * information for this import process.
	 *
	 * @since 4.6.16
	 *
	 * @param string $transitional_id
	 */
	public function set_transitional_id( $transitional_id ) {
		$this->transitional_id = $transitional_id;
	}

	/**
	 * Overrides the parent `save` method to save some additonal data.
	 *
	 * @since 4.6.16
	 *
	 * @return Tribe__Events__Aggregator__Processes__Import_Events
	 */
	public function save() {
		add_filter( "tribe_process_queue_{$this->identifier}_save_data", array( $this, 'save_data' ) );

		return parent::save();
	}

	/**
	 * Overrides the parent `update` method to save some additonal data.
	 *
	 * @since 4.6.16
	 *
	 * @return Tribe__Events__Aggregator__Processes__Import_Events
	 */
	public function update( $key, $data ) {
		add_filter( "tribe_process_queue_{$this->identifier}_update_data", array( $this, 'save_data' ) );

		return parent::update( $key, $data );
	}

	/**
	 * Saves some additional data on the record to keep track of the progress.
	 *
	 * @since 4.6.16
	 *
	 * @param array $save_data
	 *
	 * @return array
	 */
	public function save_data( array $save_data = array() ) {
		$save_data['record_id'] = $this->record_id;

		return $save_data;
	}

	/**
	 * Returns this import process record post ID.
	 *
	 * @since 4.6.16
	 *
	 * @return int
	 */
	public function get_record_id() {
		return $this->record_id;
	}

	/**
	 * Sets this import process record ID.
	 *
	 * @since 4.6.16
	 *
	 * @param int $record_id
	 */
	public function set_record_id( $record_id ) {
		$this->record_id = $record_id;
	}

	/**
	 * Handles the real import.
	 *
	 * In short: if an event has dependencies and those are not yet all in place then the event
	 * will be re-queued; otherwise it's inserted.
	 *
	 * @since 4.6.16
	 *
	 * @param array $item
	 *
	 * @return array|false Either the event data to requeue or `false` if done.
	 */
	protected function task( $item ) {
		$record_id             = $this->record_id = $item['record_id'];
		$data                  = (array) $item['data'];
		$this->transitional_id = filter_var( $item['transitional_id'], FILTER_SANITIZE_STRING );

		$dependencies = $this->parse_linked_post_dependencies( $data );

		if ( empty( $dependencies ) ) {
			$this->has_dependencies = false;
			$activity               = $this->insert_event( $record_id, (object) $data );
			$this->activities[]     = $activity;

			return $this->doing_sync ? $activity : false;
		}

		$dependencies_ids = $this->check_dependencies( $dependencies );

		if ( $dependencies_ids ) {
			$this->set_linked_posts_ids( $data, $dependencies_ids );
			$activity           = $this->insert_event( $record_id, (object) $data );
			$this->activities[] = $activity;

			return $this->doing_sync ? $activity : false;
		}

		return $item;
	}

	/**
	 * Parses the Event Venue and Organizer dependencies.
	 *
	 * @since 4.6.16
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
	 * @since 4.6.16
	 *
	 * @param int $record_id
	 * @param object $data
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity|bool Either the resulting activity or `false`
	 *                                                          if the record could not be found.
	 */
	protected function insert_event( $record_id, $data ) {
		$record = $this->get_record( $record_id );

		if ( empty( $record ) || $record instanceof WP_Error ) {
			// no point in going on
			return false;
		}

		if ( ! $this->has_dependencies ) {
			add_action( 'tribe_aggregator_after_insert_post', array( $this, 'add_transitional_data' ) );
		}

		$activity = $record->insert_posts( array( $data ) );
		$record->activity()->merge( $activity );
		$record->update_meta( 'activity', $record->activity() );

		return $activity;
	}

	/**
	 * Returns this import process record.
	 *
	 * @since 4.6.16
	 *
	 * @param int $record_id
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
	 * @since 4.6.16
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

	/**
	 * Replaces, in the event data, the unique ids of the linked posts with their post IDs.
	 *
	 * @since 4.6.16
	 *
	 * @param array $data
	 * @param array $dependencies_ids
	 *
	 * @return array
	 */
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
				$data['venue'] = (object) array( '_venue_id' => reset( $linked_post_ids ) );
			} else {
				$data[ $linked_post_key ] = array();
				foreach ( $linked_post_ids as $id ) {
					$data[ $linked_post_key ][] = (object) array( "_{$linked_post_key}_id" => $id );
				}
			}
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
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

		$record = $this->get_record( $this->record_id );

		if ( empty( $record ) || $record instanceof WP_Error ) {
			// no point in going on
			return false;
		}

		$record->set_status_as_success();
		$record->delete_meta( 'queue' );
	}
}
