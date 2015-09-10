<?php


class Tribe__Events__Changelog_Reader {
	protected $version_count = 3;
	protected $readme_file = '';

	public function __construct( $version_count = 3, $readme_file = '' ) {
		$this->version_count = (int) $version_count;
		$this->readme_file = empty( $readme_file ) ? $this->default_readme_file() : $readme_file;
	}

	protected function default_readme_file() {
		return dirname( dirname( dirname( __FILE__ ) ) ) . '/readme.txt';
	}

	public function get_changelog() {
		$contents = $this->extract_changelog_section();
		$lines = explode( "\n", $contents );

		$sections = array();
		$current_section = '';
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( substr( $line, 0, 1 ) == '=' ) {
				if ( count( $sections ) >= $this->version_count ) {
					break;
				}
				$header = trim( $line, '= ' );
				$current_section = $header;
				$sections[ $current_section ] = array();
			} elseif ( strlen( $line ) > 0 ) {
				$message = trim( $line, '* ' );
				$sections[ $current_section ][] = $message;
			}
		}
		return $sections;
	}

	protected function extract_changelog_section() {
		$contents = $this->get_readme_file_contents();
		$start = strpos( $contents, '== Changelog ==' );
		if ( $start === FALSE ) {
			return '';
		}
		$start += 16; // account for the length of the header
		$end = strpos( $contents, '==', $start );
		return trim( substr( $contents, $start, $end - $start ) );
	}

	protected function get_readme_file_contents() {
		return file_get_contents( $this->readme_file );
	}
}
