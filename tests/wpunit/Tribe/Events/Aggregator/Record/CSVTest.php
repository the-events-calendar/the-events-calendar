<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Aggregator__Record__CSV as CSV_Record;

/**
 * Tests for Tribe__Events__Aggregator__Record__CSV::get_file_path().
 *
 * Verifies that the method correctly validates file paths before use:
 *   1. Extension check  – only .csv files are permitted.
 *   2. Directory check  – the resolved path must be inside wp-content/uploads/.
 *   3. Reverse lookup   – if the file is outside uploads, an attachment DB lookup
 *                         is attempted via attachment_url_to_postid() before giving up.
 *
 * Numeric values are treated as attachment IDs and resolved via get_attached_file().
 * Both the numeric and string-path routes go through the same filetype guard.
 *
 * @group security
 * @group aggregator
 * @group csv
 */
class CSVTest extends Events_TestCase {

	/** @var string Absolute path to the WP uploads base directory. */
	private $uploads_dir;

	/** @var string[] Temporary files created during a test; cleaned up in tearDown. */
	private $temp_files = [];

	// -------------------------------------------------------------------------
	// Set-up / tear-down
	// -------------------------------------------------------------------------

	function setUp() {
		parent::setUp();

		$upload_info       = wp_upload_dir();
		$this->uploads_dir = $upload_info['basedir'];
		wp_mkdir_p( $this->uploads_dir );
	}

	public function tearDown() {
		foreach ( $this->temp_files as $path ) {
			if ( file_exists( $path ) ) {
				@unlink( $path );
			}
		}
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Builds a CSV_Record with its `file` meta already set.
	 */
	private function make_record( $file ): CSV_Record {
		$record              = new CSV_Record();
		$record->meta['file'] = $file;
		return $record;
	}

	/**
	 * Invokes the protected get_file_path() via reflection so the test does not
	 * depend on a public-facing wrapper.
	 *
	 * @return string|false
	 */
	private function call_get_file_path( CSV_Record $record ) {
		$method = new \ReflectionMethod( CSV_Record::class, 'get_file_path' );
		$method->setAccessible( true );
		return $method->invoke( $record );
	}

	/**
	 * Writes a file to $path and registers it for deletion after the test.
	 */
	private function create_file( $path, $contents = "col1,col2\nval1,val2\n" ): string {
		wp_mkdir_p( dirname( $path ) );
		file_put_contents( $path, $contents );
		$this->temp_files[] = $path;
		return $path;
	}

	// -------------------------------------------------------------------------
	// String path: non-CSV file outside the uploads directory (wp-config.php)
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_php_file_at_wordpress_root() {
		$target = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $target ) ) {
			$this->markTestSkipped( 'wp-config.php not found at ABSPATH on this host.' );
		}

		$record = $this->make_record( $target );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a .php file located outside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: non-CSV file outside the uploads directory (wp-settings.php)
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_php_file_at_wordpress_root_settings() {
		$target = ABSPATH . 'wp-settings.php';

		if ( ! file_exists( $target ) ) {
			$this->markTestSkipped( 'wp-settings.php not found at ABSPATH on this host.' );
		}

		$record = $this->make_record( $target );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a .php file located outside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: system file with no recognised extension, outside uploads
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_system_file_with_no_csv_extension() {
		$target = '/etc/passwd';

		if ( ! file_exists( $target ) ) {
			$this->markTestSkipped( '/etc/passwd does not exist on this host.' );
		}

		$record = $this->make_record( $target );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a file with no .csv extension that is outside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: wrong extension inside uploads (extension guard only)
	//
	// Places a .php file inside uploads to isolate the extension check from
	// the directory check and confirm each guard works independently.
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_non_csv_extension_even_when_file_is_inside_uploads() {
		// Place a .php file inside uploads to isolate the extension guard.
		$target = trailingslashit( $this->uploads_dir ) . 'tec-test-file.php';
		$this->create_file( $target, '<?php // test file ?>' );

		$record = $this->make_record( $target );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a .php file even when it is inside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: correct extension but outside uploads and not in the DB
	//
	// Places a .csv file in the system temp directory.  The extension guard
	// passes, but the file is outside uploads AND attachment_url_to_postid()
	// finds no matching attachment, so the method must return false.
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_csv_file_located_outside_uploads_directory() {
		$target = sys_get_temp_dir() . '/tec-test-events.csv';
		$this->create_file( $target );

		$record = $this->make_record( $target );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a .csv file that lives outside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: relative "../" segments that resolve outside uploads
	//
	// realpath() normalises ../ segments before the directory check runs,
	// so the check operates on the final resolved absolute path.
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_path_with_relative_segments_that_resolve_outside_uploads() {
		// Construct a path anchored inside uploads that navigates out via ../.
		// e.g. /var/www/html/wp-content/uploads/../../wp-config.php
		$traversal = $this->uploads_dir . '/../../wp-config.php';
		$resolved  = realpath( $traversal );

		if ( false === $resolved || ! file_exists( $resolved ) ) {
			$this->markTestSkipped( 'Path with ../ segments does not resolve to an existing file on this host.' );
		}

		$record = $this->make_record( $traversal );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a path containing ../ segments that resolve outside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// String path: valid .csv inside the uploads directory (should pass)
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_allows_valid_csv_file_inside_uploads_directory() {
		$path = trailingslashit( $this->uploads_dir ) . 'tec-test-import.csv';
		$this->create_file( $path );

		$record = $this->make_record( $path );

		$this->assertSame(
			realpath( $path ),
			$this->call_get_file_path( $record ),
			'get_file_path() must return the resolved path for a valid .csv inside the uploads directory.'
		);
	}

	// -------------------------------------------------------------------------
	// Numeric value: valid .csv attachment ID (should pass)
	//
	// When the value is numeric the file is resolved via get_attached_file().
	// The same CSV extension guard still applies to the resolved path.
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_resolves_numeric_attachment_id_for_valid_csv_in_uploads() {
		$csv_path = trailingslashit( $this->uploads_dir ) . 'tec-attachment-import.csv';
		$this->create_file( $csv_path );

		$attachment_id = $this->factory()->attachment->create_upload_object( $csv_path );

		$attached_file = get_attached_file( $attachment_id );

		if ( empty( $attached_file ) ) {
			$this->markTestSkipped( 'Could not retrieve attached file path – attachment factory may not support CSV.' );
		}

		$record = $this->make_record( (string) $attachment_id );
		$result = $this->call_get_file_path( $record );

		$this->assertNotFalse(
			$result,
			'get_file_path() must return the resolved path for a numeric attachment ID pointing to a valid .csv file.'
		);
	}

	// -------------------------------------------------------------------------
	// Numeric value: attachment ID pointing to a non-CSV file (should fail)
	//
	// The extension guard applies to the numeric branch as well, so a numeric
	// ID whose attached file is not a .csv must return false.
	// -------------------------------------------------------------------------

	/**
	 * @test
	 */
	public function it_rejects_numeric_attachment_id_pointing_to_non_csv_file() {
		$target = trailingslashit( $this->uploads_dir ) . 'tec-test-attachment.txt';
		$this->create_file( $target, 'not a csv' );

		$attachment_id = $this->factory()->attachment->create_upload_object( $target );
		$attached_file = get_attached_file( $attachment_id );

		if ( empty( $attached_file ) ) {
			$this->markTestSkipped( 'Could not create .txt attachment for this test.' );
		}

		$record = $this->make_record( (string) $attachment_id );

		$this->assertFalse(
			$this->call_get_file_path( $record ),
			'get_file_path() must return false for a numeric attachment ID whose file does not have a .csv extension.'
		);
	}
}
