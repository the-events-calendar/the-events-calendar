<?php


namespace Tribe\Events\Aggregator\Processes;


use Codeception\TestCase\WPTestCase;
use Tribe__Events__Aggregator__Record__gCal;
use Tribe__Events__Aggregator__Records;

class Batch_imports_Test extends WPTestCase {
	/**
	 * should build a url for the new EA import
	 *
	 * @dataProvider url_provider
	 *
	 * @test
	 */
	public function should_build_a_url_for_the_new_ea_import( $url, $endpoint, $expected ) {
		$imports = new Batch_Imports();
		$api     = (object) [
			'key'      => 'Random Key',
			'version'  => 'v1',
			'domain'   => 'https://ea.theeventscalendar.com/',
			'path'     => 'api/aggregator/',
			'licenses' => [],
		];

		$this->assertEquals( $expected, $imports->build_url( $url, $endpoint, $api ) );
	}

	public function url_provider() {
		return [
			[ '', '', '', ],
			[ '/', 'import', 'https://ea.theeventscalendar.com/api/aggregator/v2.0.0/import' ],
			[ '/', 'preview', '/' ],
		];
	}

	/**
	 * should  return if batch pushing is already marked as false
	 *
	 * @test
	 */
	public function should_return_if_batch_pushing_is_already_marked_as_false() {
		$imports = new Batch_Imports();

		$this->assertFalse( $imports->allow_batch_import( false, $this->create_record() ) );
	}

	/**
	 * should return early if the  record is invalid
	 *
	 * @test
	 */
	public function should_return_early_if_the_record_is_invalid() {
		$imports = new Batch_Imports();

		$this->assertTrue( $imports->allow_batch_import( true, null ) );
	}

	/**
	 * should return early if the record does not have a parent record
	 *
	 * @test
	 */
	public function should_return_early_if_the_record_does_not_have_a_parent_record() {
		$imports = new Batch_Imports();

		$this->assertTrue( $imports->allow_batch_import( true, $this->create_record() ) );
	}

	/**
	 * should return false if the parent does not support batch pushing
	 *
	 * @test
	 */
	public function should_return_false_if_the_parent_does_not_support_batch_pushing() {
		$imports = new Batch_Imports();
		$parent  = $record = $this->create_record();
		$record  = $this->create_record( [ 'parent' => $parent->post->ID ] );

		$this->assertFalse( $imports->allow_batch_import( true, $record ) );
	}

	/**
	 * should return true only if the parent has support for batch pushing
	 *
	 * @test
	 */
	public function should_return_true_only_if_the_parent_has_support_for_batch_pushing() {
		$imports = new Batch_Imports();
		$parent  = $record = $this->create_record([], ['allow_batch_push' => true]);
		$record = $this->create_record( [ 'parent' => $parent->post->ID ] );

		$this->assertTrue( $imports->allow_batch_import( true, $record ) );
	}

	/**
	 * should return false if async mode is enabled
	 *
	 * @test
	 */
	 public function should_return_false_if_async_mode_is_enabled() {
		 $imports = new Batch_Imports();
		 $parent  = $record = $this->create_record([], ['allow_batch_push' => true]);
		 $record = $this->create_record( [ 'parent' => $parent->post->ID ] );

		 add_filter(
			 'tribe_get_option',
			 function ( $value, $key ) {
				 if ( $key === 'tribe_aggregator_import_process_system' ) {
					 return 'async';
				 }

				 return $value;
			 },
			 10,
			 2
		 );

		 $this->assertFalse( $imports->allow_batch_import( true, $record ) );
	 }

	private function create_record( $args = [], $meta_args = [] ) {
		$meta = array_merge(
			[
				'import_id'   => uniqid( '', true ),
				'preview'     => false,
				'origin'      => 'gcal',
				'source_name' => 'Test Calendar',
				'source'      => 'http://some-gcal.com/ical',
			],
			$meta_args
		);

		$record = new Tribe__Events__Aggregator__Record__gCal();
		$record->create( 'manual', $args, $meta );
		$record->set_status( Tribe__Events__Aggregator__Records::$status->pending );

		return $record;
	}
}
