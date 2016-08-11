<?php
namespace Tribe\Events\Revisions;

use Tribe__Events__Main as Main;
use Tribe__Events__Meta__Save as Save;
use Tribe__Events__Revisions__Event as Event;

class EventTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var Save
	 */
	protected $meta_save;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->post      = $this->factory()->post->create_and_get( [ 'post_type' => Main::POSTTYPE ] );
		$this->meta_save = $this->prophesize( Save::class );
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

		$this->assertInstanceOf( Event::class, $sut );
	}

	/**
	 * @test
	 * it should delegate save operations to meta save class
	 */
	public function it_should_delegate_save_operations_to_meta_save_class() {
		$this->meta_save->save()->willReturn( 'foo' );

		$sut = $this->make_instance();

		$this->assertEquals( 'foo', $sut->save() );
	}

	/**
	 * @return Event
	 */
	private function make_instance() {
		return new Event( $this->post, $this->meta_save->reveal() );
	}
}