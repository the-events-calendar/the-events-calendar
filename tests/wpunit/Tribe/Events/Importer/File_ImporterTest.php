<?php

namespace Tribe\Events\Importer;

use Tribe__Events__Importer__Featured_Image_Uploader;
use Tribe__Events__Importer__File_Importer as File_Importer;
use Tribe__Events__Importer__File_Reader as File_Reader;

class File_ImporterTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->file_reader = $this->prophesize( File_Reader::class );
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( File_Importer::class, $sut );
	}

	/**
	 * @return File_Importer
	 */
	private function make_instance() {
		return new class( $this->file_reader->reveal() ) extends File_Importer {
			public function __construct( File_Reader $file_reader, Tribe__Events__Importer__Featured_Image_Uploader $featured_image_uploader = null ) {
				parent::__construct( $file_reader, $featured_image_uploader );
			}

			protected function match_existing_post( array $record ) {
				return false;
			}

			protected function update_post( $post_id, array $record ) {
				return true;
			}

			protected function create_post( array $record ) {
				return true;
			}
		};
	}

	/**
	 * It should watch and allow fetching created terms
	 *
	 * @test
	 */
	public function should_watch_and_allow_fetching_created_terms() {
		$importer = $this->make_instance();
		$importer->watch_term_creation();

		$foo = wp_create_term( 'foo', 'post_tag' );
		$bar = wp_create_term( 'bar', 'post_tag' );

		$expected = array_column( [
			$foo,
			$bar,
		], 'term_id' );

		$this->assertEquals( $expected, $importer->created_terms( 'post_tag', true )->getArrayCopy() );
		$this->assertEquals( $expected, $importer->created_terms( 'post_tag', false )->getArrayCopy() );

		$baz      = wp_create_term( 'baz', 'post_tag' );
		$expected = array_column( [
			$foo,
			$bar,
			$baz,
		], 'term_id' );

		$this->assertEquals( $expected, $importer->created_terms( 'post_tag', true )->getArrayCopy() );
		$this->assertEquals( $expected, $importer->created_terms( 'post_tag', false )->getArrayCopy() );
	}

	public function created_terms_iteration_modes() {
		return [
			'no-rewind' => [ false, 2, 3 ],
			'rewind'    => [ true, 2, 5 ],
		];
	}

	/**
	 * It should correctly allow iterating on the created terms
	 *
	 * @test
	 * @dataProvider created_terms_iteration_modes
	 */
	public function should_correctly_allow_iterating_on_the_created_terms( $rewind, $expected_first_count, $expected_second_count ) {
		$importer = $this->make_instance();
		$importer->watch_term_creation();

		$foo = wp_create_term( 'foo', 'post_tag' );
		$bar = wp_create_term( 'bar', 'post_tag' );

		$count = 0;
		foreach ( $importer->created_terms( 'post_tag', $rewind ) as $term_id ) {
			$count ++;
		}

		$this->assertEquals( $expected_first_count, $count );

		$baz  = wp_create_term( 'baz', 'post_tag' );
		$woot = wp_create_term( 'woot', 'post_tag' );
		$xoop = wp_create_term( 'xoop', 'post_tag' );

		$second_count = 0;
		foreach ( $importer->created_terms( 'post_tag', $rewind ) as $term_id ) {
			$second_count ++;
		}

		$this->assertEquals( $expected_second_count, $second_count );
	}
}