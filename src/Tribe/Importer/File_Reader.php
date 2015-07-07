<?php

/**
 * Class Tribe__Events__Importer__File_Reader
 */
class Tribe__Events__Importer__File_Reader {
	private $path = '';
	private $file = null;
	private $last_line_read = 0;

	public function __construct( $file_path ) {
		ini_set( 'auto_detect_line_endings', true );
		$this->path = $file_path;
		$this->file = new SplFileObject( $this->path );
		$this->file->setFlags( SplFileObject::SKIP_EMPTY | SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE );
	}

	public function __destruct() {
		$this->file = null;
	}

	public function get_header() {
		$this->file->rewind();
		$row = $this->file->current();

		return $row;
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
			return array();
		}
		$row = $this->file->current();
		$this->file->next();

		return empty( $row ) ? array() : $row;
	}

	public function get_last_line_number_read() {
		return $this->last_line_read;
	}

	public function at_end_of_file() {
		return ! $this->file->valid();
	}
}
