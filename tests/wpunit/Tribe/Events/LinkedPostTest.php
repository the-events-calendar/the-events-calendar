<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Linked_Posts__Base;

/**
 * Class Mock_Linked_Post
 */
class Mock_Linked_Post extends Tribe__Events__Linked_Posts__Base {

	/**
	 * @var string
	 */
	protected $meta_prefix = '_Venue';

	/**
	 * @var array A list of the valid meta keys for this linked post.
	 */
	public static $meta_keys = [
		'Address',
		'City',
		'Province',
		'State',
		'StateProvince',
		'Province',
		'Zip',
		'Phone',
	];

	protected function get_duplicate_post_fields() {
		return [
			'post_title'   => array( 'match' => 'same' ),
			'post_content' => array( 'match' => 'same' ),
			'post_excerpt' => array( 'match' => 'same' ),
		];
	}

	protected function get_duplicate_custom_fields() {
		return [
			'_VenueAddress'       => array( 'match' => 'like' ),
			'_VenueCity'          => array( 'match' => 'same' ),
			'_VenueProvince'      => array( 'match' => 'same' ),
			'_VenueState'         => array( 'match' => 'same' ),
			'_VenueStateProvince' => array( 'match' => 'same' ),
			'_VenueZip'           => array( 'match' => 'same' ),
			'_VenuePhone'         => array( 'match' => 'same' ),
		];
	}

	public function get_prefix_key( $string ) {
		return $this->prefix_key( $string );
	}
}

/**
 * Class LinkedPostTest
 *
 * @package Tribe\Events
 */
class LinkedPostTest extends WPTestCase {

	/**
	 * @test
	 */
	public function test_prefix_key_method() {
		$mocker = new Mock_Linked_Post();

		$this->assertEquals( '_VenueAddress', $mocker->get_prefix_key( 'Address' ), 'Key should be prefixed properly' );

		$this->assertEquals( '_VenueAddress', $mocker->get_prefix_key( '_VenueAddress' ), 'If key is prefixed then it should return same value' );

		$this->assertEquals( '_VenuExcel', $mocker->get_prefix_key( '_VenuExcel' ), 'If key is prefixed partially then it should return same value' );

		$this->assertEquals( 'address', $mocker->get_prefix_key( 'address' ), 'If key not found in supported keys, it should return same key' );

		$this->assertEquals( 123, $mocker->get_prefix_key( 123 ), 'If key not found in supported keys, it should return same key' );

		$this->assertEquals( '', $mocker->get_prefix_key( '' ), 'If key not found in supported keys, it should return same key' );

		$this->assertNull( $mocker->get_prefix_key( null ), 'If key not found in supported keys, it should return same key' );
	}
}
