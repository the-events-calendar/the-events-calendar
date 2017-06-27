<?php

namespace Tribe\Events;

use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Venue as Venue;

class VenueTest extends Events_TestCase {
	/**
	 * It should allow searching like in title
	 * @test
	 */
	public function it_should_allow_searching_like_in_title() {
		$venue_1 = $this->factory()->venue->create( [ 'post_title' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_title' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_title' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in content
	 * 
	 * @test
	 */
	public function should_allow_searching_like_in_content() {
		$venue_1 = $this->factory()->venue->create( [ 'post_content' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_content' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_content' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in excerpt 
	 * 
	 * @test
	 */
	public function should_allow_searching_like_in_excerpt() {
		$venue_1 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in title, content and excerpt 
	 * 
	 * @test
	 */
	public function should_allow_searching_like_in_title_content_and_excerpt() {
		$venue_1 = $this->factory()->venue->create( [
			'post_excerpt' => 'Venue Foo',
			'post_content' => 'lorem dolor',
			'post_excerpt' => 'sed nunqua',
		] );
		$venue_2 = $this->factory()->venue->create( [ 
			'post_excerpt' => 'Venue Bar',
			'post_content' => 'dolor sit',
			'post_excerpt' => 'altera via',
		] );
		$venue_3 = $this->factory()->venue->create( [ 
			'post_excerpt' => 'Venue Baz',
			'post_content' => 'sit nunqua',
			'post_excerpt' => 'Caesar docet',
		] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 2, $sut->find_like( 'dolor' ) );
		$this->assertCount( 2, $sut->find_like( 'sit' ) );
		$this->assertCount( 1, $sut->find_like( 'caesar' ) );
		$this->assertCount( 2, $sut->find_like( 'nunqua' ) );
	}

	/**
	 * It should allow searching like in custom fields
	 * 
	 * @test
	 */
	public function should_allow_searching_like_in_custom_fields()
	{
		$venue_1 = $this->factory()->venue->create( [
			'post_title' => 'Venue Foo',
			'post_content' => 'lorem dolor',
			'post_excerpt' => 'sed nunqua',
			'meta_input' => [
				'_VenueAddress'       =>'221b Baker Street',
				'_VenueCity'          =>'London',
				'_VenueProvince'      =>'Greater London',
				'_VenueState'         =>'England',
				'_VenueStateProvince' =>'England, Greater London',
				'_VenueZip'           =>'223345',
				'_VenuePhone'         =>'111111',
			],
		] );
		$venue_2 = $this->factory()->venue->create( [ 
			'post_title' => 'Venue Bar',
			'post_content' => 'dolor sit',
			'post_excerpt' => 'altera via',
			'meta_input' => [
				'_VenueAddress'       =>'10, Piccadilly Circus',
				'_VenueCity'          =>'London',
				'_VenueProvince'      =>'Greater London',
				'_VenueState'         =>'England',
				'_VenueStateProvince' =>'England, Greater London',
				'_VenueZip'           =>'223345',
				'_VenuePhone'         =>'22222222',
			],
		] );
		$venue_3 = $this->factory()->venue->create( [ 
			'post_title' => 'Venue Baz',
			'post_content' => 'sit nunqua',
			'post_excerpt' => 'Caesar docet',
			'meta_input' => [
				'_VenueAddress'       =>'100, Avenue du Temple',
				'_VenueCity'          =>'Paris',
				'_VenueProvince'      =>'Ile de France',
				'_VenueState'         =>'France',
				'_VenueStateProvince' =>'France, Ile de France',
				'_VenueZip'           =>'23443',
				'_VenuePhone'         =>'3333333',
			],
		] );

		$sut = $this->make_instance();

		$this->assertCount( 2, $sut->find_like( 'london' ) );
		$this->assertCount( 1, $sut->find_like( '3333333' ) );
		$this->assertCount( 1, $sut->find_like( 'temple avenue' ) );
		$this->assertCount( 2, $sut->find_like( 'greater london' ) );
		$this->assertCount( 1, $sut->find_like( 'france' ) );
		$this->assertCount( 1, $sut->find_like( 'france, ile de' ) );
		$this->assertCount( 2, $sut->find_like( '223345' ) );
	}

	/**
	 * @return Venue
	 */
	protected function make_instance() {
		return tribe( 'tec.linked-posts.venue' );
	}

}