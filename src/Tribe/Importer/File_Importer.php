<?php

/**
 * Class Tribe__Events__Importer__File_Importer
 */
abstract class Tribe__Events__Importer__File_Importer {
	protected $required_fields = [];

	/**
	 * An array that keeps tracks of the terms created for a taxonomy in the shape
	 * [ <taxonomy> => ArrayIterator( [ <term_id>, ... ] ) ].
	 *
	 * @var array
	 */
	protected $created_terms = [];

	/** @var Tribe__Events__Importer__File_Reader */
	private $reader   = null;
	private $map = [];
	private $type     = '';
	private $limit    = 100;
	private $offset   = 0;
	private $errors = [];
	private $updated  = 0;
	private $created  = 0;
	private $encoding = [];
	protected $log = [];

	protected $skipped = [];
	protected $inverted_map = [];

	public $is_aggregator = false;
	public $aggregator_record;
	public $default_category;
	public $default_post_status;

	/**
	 * @var Tribe__Events__Importer__Featured_Image_Uploader
	 */
	protected $featured_image_uploader;

	/**
	 * @param string                         $type
	 * @param Tribe__Events__Importer__File_Reader $file_reader
	 *
	 * @return Tribe__Events__Importer__File_Importer
	 * @throws InvalidArgumentException
	 */
	public static function get_importer( $type, Tribe__Events__Importer__File_Reader $file_reader ) {
		switch ( $type ) {
			case 'event':
			case 'events':
				return new Tribe__Events__Importer__File_Importer_Events( $file_reader );
			case 'venue':
			case 'venues':
				return new Tribe__Events__Importer__File_Importer_Venues( $file_reader );
			case 'organizer':
			case 'organizers':
				return new Tribe__Events__Importer__File_Importer_Organizers( $file_reader );
			default:
				/**
				 * Allows developers to return an importer instance to use for unsupported import types.
				 *
				 * @param bool|mixed An importer instance or `false` if not found or not supported.
				 * @param Tribe__Events__Importer__File_Reader $file_reader
				 */
				$importer = apply_filters( "tribe_events_import_{$type}_importer", false, $file_reader );

				if ( false === $importer ) {
					throw new InvalidArgumentException( sprintf( esc_html__( 'No importer defined for %s', 'the-events-calendar' ), $type ) );
				}

				return $importer;
		}
	}

	/**
	 * @param Tribe__Events__Importer__File_Reader $file_reader
	 */
	public function __construct( Tribe__Events__Importer__File_Reader $file_reader, Tribe__Events__Importer__Featured_Image_Uploader $featured_image_uploader = null ) {
		$this->reader                  = $file_reader;
		$this->featured_image_uploader = $featured_image_uploader;
		$this->limit                   = apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size );
	}

	public function set_map( array $map_array ) {
		$this->map          = $map_array;
		$this->inverted_map = array_flip( $this->map );
	}

	public function set_type( $type ) {
		$this->type = $type;
	}

	public function set_limit( $limit ) {
		$this->limit = (int) $limit;
	}

	public function set_offset( $offset ) {
		$this->offset = (int) $offset;
	}

	public function do_import() {
		$this->reader->set_row( $this->offset );
		for ( $i = 0; $i < $this->limit && ! $this->import_complete(); $i ++ ) {
			tribe_set_time_limit( 30 );
			$this->import_next_row();
		}
	}

	public function do_import_preview() {
		$rows = [];

		$this->reader->set_row( $this->offset );
		for ( $i = 0; $i < $this->limit && ! $this->import_complete(); $i ++ ) {
			tribe_set_time_limit( 30 );
			$rows[] = $this->import_next_row( false, true );
		}

		return $rows;
	}

	public function get_last_completed_row() {
		return $this->reader->get_last_line_number_read() + 1;
	}

	public function import_complete() {
		return $this->reader->at_end_of_file();
	}

	public function get_line_count() {
		return $this->reader->lines;
	}

	public function get_updated_post_count() {
		return $this->updated;
	}

	public function get_new_post_count() {
		return $this->created;
	}

	public function get_skipped_row_count() {
		return count( $this->skipped );
	}

	public function get_skipped_row_numbers() {
		return $this->skipped;
	}

	public function get_encoding_changes_row_count() {
		return count( $this->encoding );
	}

	public function get_encoding_changes_row_numbers() {
		return $this->encoding;
	}

	public function get_log_messages() {
		return $this->log;
	}

	public function get_required_fields() {
		return $this->required_fields;
	}

	public function get_type() {
		return $this->type;
	}

	public function import_next_row( $throw = false, $preview = false ) {
		$post_id = null;
		$record = $this->reader->read_next_row();
		$row    = $this->reader->get_last_line_number_read() + 1;

		//Check if option to encode is active
		$encoding_option = Tribe__Settings_Manager::get_option( 'imported_encoding_status', [ 'csv' => 'encode' ] );
		/**
		 *  Filter Encoding Status Option for CSV Imports
		 *
		 * @since 4.6.18
		 *
		 * @param  $encoding_status array an array to encode
		 * @param [ 'csv' => 'encode' ] array the default value to enable encoding to UTF8
		 */
		$encoding_option = apply_filters( 'tribe_import_setting_imported_encoding_status', $encoding_option, [ 'csv' => 'encode' ] );

		if ( isset( $encoding_option['csv'] ) && 'encode' == $encoding_option['csv'] ) {
			$encoded       = ForceUTF8__Encoding::toUTF8( $record );
			$encoding_diff = array_diff( $encoded, $record );
			if ( ! empty( $encoding_diff ) ) {
				$this->encoding[] = $row;
			}
			$record = $encoded;
		}

		if ( $preview ) {
			return $record;
		}

		if ( ! $this->is_valid_record( $record ) ) {
			if ( ! $throw ) {
				$this->log[ $row ] = $this->get_skipped_row_message( $row );
				$this->skipped[]   = $row;

				return false;
			} else {
				throw new RuntimeException( sprintf( 'Missing required fields in row %d', $row ) );
			}
		}

		try {
			$post_id = $this->update_or_create_post( $record );
		} catch ( Exception $e ) {
			$this->log[ $row ] = sprintf( esc_html__( 'Failed to import record in row %d.', 'the-events-calendar' ), $row );
			$this->skipped[] = $row;
		}

		return $post_id;
	}

	protected function update_or_create_post( array $record ) {
		if ( $id = $this->match_existing_post( $record ) ) {
			if ( false !== $this->update_post( $id, $record ) ) {
				$this->updated ++;
				$this->log[ $this->reader->get_last_line_number_read() + 1 ] = sprintf( esc_html__( '%s (post ID %d) updated.', 'the-events-calendar' ), get_the_title( $id ), $id );
			}
		} else {
			$id = $this->create_post( $record );
			$this->created ++;
			$this->log[ $this->reader->get_last_line_number_read() + 1 ] = sprintf( esc_html__( '%s (post ID %d) created.', 'the-events-calendar' ), get_the_title( $id ), $id );
		}

		$featured_image = $this->get_value_by_key( $record, 'featured_image' );

		do_action( 'tribe_log', 'debug', __CLASS__, [ 'post_id' => $id, 'featured_image' => $featured_image ] );

		if ( ! empty( $featured_image ) ) {
			$post_thumbnail_process = new Tribe__Process__Post_Thumbnail_Setter();
			$post_thumbnail_process->set_post_id( $id );
			$post_thumbnail_process->set_post_thumbnail( $featured_image );
			$post_thumbnail_process->dispatch();
		}

		/**
		 * Hook after an event is updated or created by the csv importer.
		 *
		 * @since 5.12.4
		 *
		 * @param integer                                $id     The event ID to update.
		 * @param array<string|mixed>                    $record An event record from the import.
		 * @param Tribe__Events__Importer__File_Importer $this   An instance of the Tribe__Events__Importer__File_Importer class.
		 */
		do_action( 'tec_events_csv_importer_post_update', $id, $record, $this );

		return $id;
	}

	abstract protected function match_existing_post( array $record );

	abstract protected function update_post( $post_id, array $record );

	abstract protected function create_post( array $record );

	protected function is_valid_record( array $record ) {
		foreach ( $this->get_required_fields() as $field ) {
			if ( $this->get_value_by_key( $record, $field ) == '' ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves a value from the record.
	 *
	 * @since 5.1.6 - modify to use has_value_by_key().
	 *
	 * @param array  $record An event record from the import.
	 * @param string $key    The text of the key to find in the record array.
	 *
	 * @return mixed|string Either the value or an empty string if the value was not found.
	 */
	public function get_value_by_key( array $record, $key ) {
		if ( ! $this->has_value_by_key( $record, $key ) ) {
			return '';
		}

		return $record[ $this->inverted_map[ $key ] ];
	}

	/**
	 * Check if a key is found.
	 *
	 * @since 5.1.6
	 *
	 * @param array  $record An event record from the import.
	 * @param string $key    The text of the key to find in the record array.
	 *
	 * @return bool Whether the key is found in the record.
	 */
	public function has_value_by_key( array $record, $key ) {
		if ( ! isset( $this->inverted_map[ $key ] ) ) {
			return false;
		}
		if ( ! isset( $record[ $this->inverted_map[ $key ] ] ) ) {
			return false;
		}

		return true;
	}

	protected function find_matching_post_id( $name, $post_type, $post_status = 'publish' ) {
		if ( empty( $name ) ) {
			return 0;
		}

		if ( is_numeric( $name ) && intval( $name ) == $name ) {
			$found = get_post( $name );
			if ( $found && $found->post_type == $post_type ) {
				return $name;
			}
		}

		$query_args = [
			'post_type'        => $post_type,
			'post_status'      => $post_status,
			'post_title'       => $name,
			'fields'           => 'ids',
			'suppress_filters' => false,
		];
		add_filter( 'posts_search', [ $this, 'filter_query_for_title_search' ], 10, 2 );
		$ids = get_posts( $query_args );
		remove_filter( 'posts_search', [ $this, 'filter_query_for_title_search' ], 10 );

		return empty( $ids ) ? 0 : reset( $ids );
	}

	public function filter_query_for_title_search( $search, WP_Query $wp_query ) {
		$title = $wp_query->get( 'post_title' );
		if ( ! empty( $title ) ) {
			global $wpdb;
			$search .= $wpdb->prepare( " AND {$wpdb->posts}.post_title=%s", $title );
		}

		return $search;
	}

	/**
	 * @param string|int $featured_image Either an absolute path to an image or an attachment ID.
	 *
	 * @return Tribe__Events__Importer__Featured_Image_Uploader
	 */
	protected function featured_image_uploader( $featured_image ) {
		// Remove any leading/trailing whitespace (if the string is a URL, extra whitespace
		// could result in URL validation fail)
		if ( is_string( $featured_image ) ) {
			$featured_image = trim( $featured_image );
		}

		return empty( $this->featured_image_uploader )
			? new Tribe__Events__Importer__Featured_Image_Uploader( $featured_image )
			: $this->featured_image_uploader;
	}

	/**
	 * Returns a boolean value from the record.
	 *
	 * @param array  $record
	 * @param string $key
	 * @param string $return_true_value    The value to return if the value was found and is truthy.
	 * @param string $return_false_value   The value to return if the value was not found or is not truthy;
	 *                                     defaults to the original value.
	 * @param array  $accepted_true_values An array of values considered truthy.
	 *
	 * @return string
	 */
	public function get_boolean_value_by_key( $record, $key, $return_true_value = '1', $return_false_value = null, $accepted_true_values = [ 'yes', 'true', '1', ] ) {
		$value = strtolower( $this->get_value_by_key( $record, $key ) );
		if ( in_array( $value, $accepted_true_values ) ) {
			return $return_true_value;
		}

		return is_null( $return_false_value ) ? $value : $return_false_value;
	}

	/**
	 * @param $row
	 *
	 * @return string
	 */
	protected function get_skipped_row_message( $row ) {
		return sprintf( esc_html__( 'Missing required fields in row %d.', 'the-events-calendar' ), $row );
	}

	/**
	 * @param       $event_id
	 * @param array $record
	 *
	 * @return bool|int|mixed|null
	 */
	protected function get_featured_image( $event_id, array $record ) {
		$featured_image_content = $this->get_value_by_key( $record, 'featured_image' );
		$featured_image         = null;
		if ( ! empty( $event_id ) ) {
			$featured_image = get_post_meta( $event_id, '_wp_attached_file', true );
			if ( empty( $featured_image ) ) {
				$featured_image = $this->featured_image_uploader( $featured_image_content )->upload_and_get_attachment_id();

				return $featured_image;
			}

			return $featured_image;
		} else {
			$featured_image = $this->featured_image_uploader( $featured_image_content )->upload_and_get_attachment_id();

			return $featured_image;

		}
	}

	/**
	 * Hooks on term creation to log it.
	 *
	 * @since 4.6.24
	 *
	 * @param int    $term_id  The newly created term ID.
	 * @param int    $tt_id    The newly created term taxonomy ID.
	 * @param string $taxonomy The current taxonomy.
	 */
	public function on_created_term( $term_id, $tt_id, $taxonomy ) {
		if ( ! isset( $this->created_terms[ $taxonomy ] ) ) {
			$this->created_terms[ $taxonomy ] = new ArrayIterator();
		}

		$this->created_terms[ $taxonomy ]->append( $term_id );
	}

	/**
	 * Hooks on the term creation to watch for any newly created terms.
	 *
	 * @since 4.6.24
	 */
	public function watch_term_creation() {
		if ( has_action( 'created_term', [ $this, 'on_created_term' ] ) ) {
			return;
		}

		add_action( 'created_term', [ $this, 'on_created_term' ], 10, 3 );
	}

	/**
	 * Stops watching for term creation and logging.
	 *
	 * @since 4.6.24
	 */
	public function stop_watching_term_creation() {
		if ( ! has_action( 'created_term', [ $this, 'on_created_term' ] ) ) {
			return;
		}

		remove_action( 'created_term', [ $this, 'on_created_term' ] );
	}

	/**
	 * Returns an iterator to iterate over the last created terms.
	 *
	 * @since 4.6.24
	 *
	 * By default a NoRewindIterator will be returned, this will allow successive calls from iterating code,
	 * e.g. a `foreach`, to resume from the previously last position.
	 *
	 * @param string $taxonomy The taxonomy to fetch the created terms for.
	 * @param bool   $rewind   Whether to return a rewinding iterator (`true`) or a NoRewind one (`false`);
	 *                         defaults to `false`.
	 *
	 * @return ArrayIterator|NoRewindIterator An ArrayIterator built on the term IDs created for the taxonomy
	 *                                        or a NoRewindIterator built on top of it.
	 */
	public function created_terms( $taxonomy, $rewind = false ) {
		$iterator = Tribe__Utils__Array::get( $this->created_terms, $taxonomy, new ArrayIterator() );

		return $rewind ? $iterator : new NoRewindIterator( $iterator );
	}
}
