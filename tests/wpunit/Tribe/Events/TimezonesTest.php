<?php
namespace Tribe\Events;

use Tribe__Events__Timezones as Timezones;
use Tribe\Events\Tests\Factories\Event;

class TimezonesTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp() {
        // before
        parent::setUp();

        // your set up methods here
        $this->factory()->event = new Event();
    }

    public function test_append_timzeone_should_append_correct_abbreviation_in_sitewide_mode() {

        $event_id = $this->factory()->event->create( [
            'when'       => '2018-05-01 08:00:00',
            'meta_input' => [
                '_EventTimezoneAbbr' => 'CEST'
            ]
        ] );

        update_option( 'timezone_string', 'America/New_York' );

        // Sitewide timezone everywhere
        tribe_update_option( 'tribe_events_timezone_mode', 'site' );

        $output = Timezones::append_timezone( '', $event_id );
        
        $this->assertEquals( " <span class='timezone'> EDT </span>", $output );
    }

    public function test_append_timzeone_should_append_correct_abbreviation_in_event_mode() {

        $event_id = $this->factory()->event->create( [
            'when'       => '2018-05-01 08:00:00',
            'meta_input' => [
                '_EventTimezoneAbbr' => 'CEST'
            ]
        ] );

        update_option( 'timezone_string', 'America/New_York' );

        // Local event timezone
        tribe_update_option( 'tribe_events_timezone_mode', 'event' );
        
        $output = Timezones::append_timezone( '', $event_id );

        $this->assertEquals( " <span class='timezone'> CEST </span>", $output );
    }
}