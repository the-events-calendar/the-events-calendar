<?php

/**
 * Class Tribe__Events__Importer__File_Reader
 */
class Tribe__Events__Importer__File_Reader {
	private $path = '';
	private $file = null;
	private $last_line_read = 0;
	public $lines;

	/**
	 * Construct for Tribe__Events__Importer__File_Reader.
	 *
	 * @since 5.14.2 - Fix for PHP 8.0.15 to 8.0.17 on getting the last line of the csv file.
	 *
	 * @param string $file_path The full path to the file.
	 */
	public function __construct( $file_path ) {
		ini_set( 'auto_detect_line_endings', true );
		$this->path = $file_path;
		$this->file = new SplFileObject( $this->path );
		$this->file->setFlags( SplFileObject::SKIP_EMPTY | SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE );
		$this->set_csv_params( $this->get_csv_params() );
		$this->file->seek( PHP_INT_MAX );
		$total_lines = $this->file->key();
		/*
		 * In PHP 8.0.15 to 8.0.17 or 8.1.2 to 8.1.4 the use of seek() and then key() returns 0 when using the flag SplFileObject::READ_CSV.
		 * This bug is fixed in PHP 8.0.18 and 8.1.5.
		 * @see https://github.com/php/php-src/issues/8236 - outlines the issue with seek().
		 * @see https://github.com/php/php-src/pull/8138 - PR to fix the issue.
		 */
		if ( 0 === $total_lines ) {
			$total_lines = iterator_count( $this->file );
		}
		$this->lines = $total_lines;
		$this->file->rewind();

		add_filter( 'tribe_events_import_row', [ $this, 'sanitize_row' ] );
	}

	public function __destruct() {
		$this->file = null;
	}

	public function get_header() {
		$this->file->rewind();

		return $this->file->current();
	}

	public function set_row( $row_number ) {
		$this->file->seek( $row_number );
	}

	public function read_row( $row_number ) {
		$this->set_row( $row_number );

		return $this->read_next_row();
	}

	public function read_next_row() {
		$this->last_line_read = $this->file->key();
		if ( ! $this->file->valid() ) {
			return [];
		}
		$row = $this->file->current();

		/**
		 * Allows for filtering the row for import
		 *
		 * @since 4.5.5
		 *
		 * @param array $row
		 */
		$row = apply_filters( 'tribe_events_import_row', $row );

		$this->file->next();

		return empty( $row ) ? [] : $row;
	}

	public function get_last_line_number_read() {
		return $this->last_line_read;
	}

	public function at_end_of_file() {
		return ! $this->file->valid();
	}

	/**
	 * Sanitizes a row
	 *
	 * @since 4.5.5
	 *
	 * @param array $row Import row
	 */
	public function sanitize_row( $row ) {
		return array_map( 'wp_kses_post', $row );
	}

	/**
	 * Get the field parameters used for reading CSV files.
	 *
	 * @since 4.6.1
	 *
	 * @return array The CSV field parameters.
	 */
	public function get_csv_params() {
		$csv_params = [
			'delimiter' => ',',
			'enclosure' => '"',
			'escape'    => '\\',
		];

		/**
		 * Set the parameters used for reading and importing CSV files.
		 *
		 * @see `SplFileObject::setCsvControl()`.
		 *
		 * @since 4.6.1
		 *
		 * @param array $csv_params params [
		 *      string $delimiter  The field delimiter (one character only).
		 *      string $enclosure The field enclosure character (one character only).
		 *      string $escape    The field escape character (one character only).
		 * ]
		 * @param string $file_path The path to the CSV file
		 */
		return apply_filters( 'tribe_events_csv_import_file_parameters', $csv_params, $this->path );
	}

	/**
	 * Set the import params for CSV fields
	 *
	 * @since 4.6.1
	 *
	 * @param array $params [
	 *      string $delimiter  The field delimiter (one character only).
	 *      string $enclosure The field enclosure character (one character only).
	 *      string $escape    The field escape character (one character only).
	 * ]
	 *
	 */
	private function set_csv_params( $params ) {
		$this->file->setCsvControl(
			$params['delimiter'],
			$params['enclosure'],
			$params['escape']
		);
	}
}
