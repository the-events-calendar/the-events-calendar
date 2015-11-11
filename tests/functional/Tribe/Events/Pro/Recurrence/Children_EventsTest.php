<?php
namespace Tribe\Events\Pro\Recurrence;

use Prophecy\Argument;
use Tribe__Cache as Cache;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Children_Events as Children_Events;

class Children_EventsTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * is instantiatable
	 */
	public function test_is_instantiatable() {
		$this->assertInstanceOf( 'Tribe__Events__Pro__Recurrence__Children_Events', new Children_Events() );
	}

	/**
	 * get_ids will hit cache by default
	 */
	public function test_get_ids_will_hit_cache_by_default() {
		$post_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$cache   = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( array() )->shouldBeCalled();


		$sut = new Children_Events( $cache->reveal() );
		$sut->get_ids( $post_id );
	}

	/**
	 * get_ids will update cache by default
	 */
	public function test_get_ids_will_update_cache_by_default() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5 );
		$cache    = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( false );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldBeCalled();

		$sut = new Children_Events( $cache->reveal() );
		$sut->get_ids( $post_id );
	}

	/**
	 * get_ids allows disabling cache hit using use_cache falsy arg
	 */
	public function test_get_ids_allows_disabling_cache_hit_using_use_cache_falsy_arg() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5 );
		$cache    = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->shouldNotBeCalled();
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldNotBeCalled();

		$sut = new Children_Events( $cache->reveal() );
		$sut->get_ids( $post_id, [ 'use_cache' => false ] );
	}

	/**
	 * get_ids will return cached value if any
	 */
	public function test_get_ids_will_return_cached_value_if_any() {
		$post_id         = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children        = $this->create_many_children( $post_id, 5 );
		$cache           = $this->prophesize( 'Tribe__Cache' );
		$cached_children = [ '12023', '12024', '12025' ];
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( $cached_children );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldNotBeCalled();

		$sut = new Children_Events( $cache->reveal() );
		$ids = $sut->get_ids( $post_id );

		$this->assertEquals( $cached_children, $ids );
	}

	/**
	 * get_ids will unset fields args param if any
	 */
	public function test_get_ids_will_unset_fields_args_param_if_any() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5 );
		$cache    = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( false );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldBeCalled();

		$sut = new Children_Events( $cache->reveal() );
		$ids = $sut->get_ids( $post_id, [ 'fields' => 'id=>parent' ] );
	}

	/**
	 * untrash_all untrashes all children
	 */
	public function test_untrash_all_untrashes_all_children() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5, [ 'post_status' => 'publish' ] );
		foreach ( $children as $child ) {
			wp_trash_post( $child );
		}
		$cache = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( false );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldBeCalled();


		$sut = new Children_Events( $cache->reveal() );
		$sut->untrash_all( $post_id );

		foreach ( $children as $child ) {
			$this->assertEquals( 'publish', get_post( $child )->post_status );
		}
	}

	/**
	 * trash_all trashes all children
	 */
	public function test_trash_all_trashes_all_children() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5 );
		$cache    = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( false );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldBeCalled();


		$sut = new Children_Events( $cache->reveal() );
		$sut->trash_all( $post_id );

		foreach ( $children as $child ) {
			$this->assertEquals( 'trash', get_post( $child )->post_status );
		}
	}

	/**
	 * permanently_delete_all deletes all children
	 */
	public function test_permanently_delete_all_deletes_all_children() {
		$post_id  = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$children = $this->create_many_children( $post_id, 5 );
		$cache    = $this->prophesize( 'Tribe__Cache' );
		$cache->get( 'child_events_' . $post_id, 'save_post' )->willReturn( false );
		$cache->set( 'child_events_' . $post_id, $children, Cache::NO_EXPIRATION, 'save_post' )->shouldBeCalled();


		$sut = new Children_Events( $cache->reveal() );
		$sut->permanently_delete_all( $post_id );

		foreach ( $children as $child ) {
			$this->assertEmpty( get_post( $child ) );
		}
	}

	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public function create_many_children( $post_id, $count, array $args = array() ) {
		$arr      = [
			'post_type'   => Main::POSTTYPE,
			'post_parent' => $post_id,
		];
		$children = $this->factory()->post->create_many( $count, array_merge( $arr, $args ) );
		array_map( function ( $id ) {
			update_post_meta( $id, '_EventStartDate', 'foo' );
		}, $children );

		return $children;
	}

}