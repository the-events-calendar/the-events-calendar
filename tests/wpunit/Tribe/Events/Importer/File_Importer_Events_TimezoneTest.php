<?php
namespace Tribe\Events\Importer;
require_once 'File_Importer_EventsTest.php';

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use org\bovigo\vfs\vfsStream;

class File_Importer_Events_TimezoneTest extends File_Importer_EventsTest {

	/**
	 * @test
	 * it should not require the timezone when importing an event
	 */
	public function it_should_not_require_the_timezone_when_importing_an_event() {
		$sut = $this->make_instance();

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	public function location_timezones() {
		return [
			[ 'Africa/Asmara', 'Africa/Asmara' ],
			[ 'Africa/Nouakchott', 'Africa/Nouakchott' ],
			[ 'America/Belem', 'America/Belem' ],
			[ 'America/Indiana/Vincennes', 'America/Indiana/Vincennes' ],
			[ 'Europe/Nicosia', 'Europe/Nicosia' ],
			[ 'Europe/Zurich', 'Europe/Zurich' ],
			[ 'Pacific/Apia', 'Pacific/Apia' ],
			[ 'Pacific/Saipan', 'Pacific/Saipan' ],
			[ 'Atlantic/Bermuda', 'Atlantic/Bermuda' ],
			[ 'Atlantic/Reykjavik', 'Atlantic/Reykjavik' ],
			[ 'America/Los_Angeles', 'America/Los_Angeles' ],
			[ 'CST6CDT', 'CST6CDT' ],
			[ 'EST', 'EST' ],
			[ 'GMT', 'GMT' ],
			[ 'GMT+0', 'GMT+0' ],
			[ 'GMT+3', 'GMT+3' ],
			[ 'GMT-3', 'GMT-3' ],
			[ 'UTC', 'UTC' ],
		];
	}

	/**
	 * @test
	 * it should import PHP supported timezones
	 * @dataProvider location_timezones
	 */
	public function it_should_import_php_supported_timezones( $timezone, $expected_timezone ) {
		$this->data        = [
			'timezone_1' => $timezone,
		];
		$this->field_map[] = 'event_timezone';

		$sut = $this->make_instance( 'timezones' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( $expected_timezone, get_post_meta( $post_id, '_EventTimezone', true ) );
	}

	public function manual_offsets() {
		return [
			[ 'UTC+0', 'UTC+0' ],
			[ 'UTC-4:30', 'UTC-4:30' ],
			[ 'UTC+10', 'UTC+10' ],
			[ 'UTC+5:30', 'UTC+5:30' ],
		];
	}

	/**
	 * @test
	 * it should support manual offsets
	 * @dataProvider manual_offsets
	 */
	public function it_should_support_manual_offsets( $timezone, $expected_timezone ) {
		$this->data        = [
			'timezone_1' => $timezone,
		];
		$this->field_map[] = 'event_timezone';

		$sut = $this->make_instance( 'timezones' );

		$post_id = $sut->import_next_row();

		$this->assertEquals( $expected_timezone, get_post_meta( $post_id, '_EventTimezone', true ) );
	}

	public function weird_timezones() {
		return [
			[ 'MyTimezone', '' ],
			[ 'Not/ATimezone', '' ],
			[ 'GMY', '' ],
			[ 'Italy/Bologna', '' ],
		];
	}

	/**
	 * @test
	 * it should not import not supported and not manual offset timezones
	 * @dataProvider weird_timezones
	 */
	public function it_should_not_import_not_supported_and_not_manual_offset_timezones( $timezone, $expected_timezone ) {
		$this->data        = [
			'timezone_1' => $timezone,
		];
		$this->field_map[] = 'event_timezone';

		$sut = $this->make_instance( 'timezones' );

		$post_id = $sut->import_next_row();

		// expect empty
		$this->assertEmpty( $expected_timezone );

		//expect timezone set to default WordPress install
		$this->assertTrue( is_string( get_post_meta( $post_id, '_EventTimezone', true ) )  );
	}
}