<?php
namespace Tribe\Events\Pro\Recurrence;

use Tribe__Events__Pro__Recurrence__Scripts as Scripts;

class ScriptsTest extends \Codeception\TestCase\WPTestCase {

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
	 * localize will create recurrence key if missing
	 */
	public function test_localize_will_create_recurrence_key_if_missing() {
		$sut = new Scripts();

		$out = $sut->localize( [ ], 'Foo', 'some_handle' );

		$this->assertArrayHasKey( 'recurrence', $out );
	}

	/**
	 * localize will keep previously existing recurrence data
	 */
	public function test_localize_will_keep_previously_existing_recurrence_data() {
		$sut = new Scripts();

		$out = $sut->localize( ['recurrence'=>['foo'=>'bar'] ], 'Foo', 'some_handle' );

		$this->assertArrayHasKey( 'recurrence', $out );
		$this->assertArrayHasKey('foo',$out['recurrence']);
		$this->assertEquals('bar',$out['recurrence']['foo']);
	}
}