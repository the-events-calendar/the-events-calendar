<?php

use Tribe__Events__Admin__Timezone_Updater as Timezone_Updater;
use Tribe__Events__Main as TEC;
use Tribe__Timezones as Timezones;
use Tribe__Date_Utils as Date_Utils;

class Timezone_UpdaterTest extends \Codeception\TestCase\WPTestCase {
    /**
     * @var Timezone_Updater
     */
    private $updater;

    /**
     * @var array
     */
    private $event_ids = [];

    public function setUp(): void {
		parent::setUp();

        // Create our updater instance
        $this->updater = new Timezone_Updater();

        // Set a consistent time zone for testing
        update_option('timezone_string', 'America/New_York');

        // Reset the "update needed" option to ensure a clean state
        tec_timed_option()->delete('events_timezone_update_needed');
    }

    public function tearDown(): void {
        // Clean up created events
        foreach ($this->event_ids as $event_id) {
            wp_delete_post($event_id, true);
        }

        parent::tearDown();
    }

	/**
     * Helper function to create test events without time zone data
     */
    private function create_event_without_timezone($start_date = '2024-03-15 10:00:00', $end_date = '2024-03-15 12:00:00'): int {
        $event_data = [
            'post_title' => 'Test Event',
            'post_type' => TEC::POSTTYPE,
            'post_status' => 'publish',
        ];

        $event_id = wp_insert_post($event_data);

        // Add event dates without time zone data
        update_post_meta($event_id, '_EventStartDate', $start_date);
        update_post_meta($event_id, '_EventEndDate', $end_date);

        $this->event_ids[] = $event_id;

        return $event_id;
    }

    public function test_update_needed_should_return_true_when_events_lack_timezone(): void {
        // Create an event without time zone data
        $this->create_event_without_timezone();

        // Should detect that an update is needed
        $this->assertTrue($this->updater->update_needed(), 'Update needed should return true when events lack time zone data');
    }

    public function test_update_needed_should_return_false_when_all_events_have_timezone(): void {
        // Create an event with time zone data
        $event_id = $this->create_event_without_timezone();

        // Add time zone data manually
        update_post_meta($event_id, '_EventTimezone', 'America/New_York');

        // Should detect that no update is needed
        $this->assertFalse($this->updater->update_needed(), 'Update needed should return false when all events have time zone data');
    }

    public function test_count_ids_should_return_correct_count(): void {
        // Create multiple events without a time zone
        $this->create_event_without_timezone();
        $this->create_event_without_timezone();
        $this->create_event_without_timezone();

        // Should find exactly 3 events needing updates
        $this->assertEquals(3, $this->updater->count_ids(), 'Count IDs should return 3 when 3 events lack time zone data');
    }

    public function test_get_ids_should_return_correct_events(): void {
        // Create events without a time zone
        $event1 = $this->create_event_without_timezone();
        $event2 = $this->create_event_without_timezone();

        // Create an event with time zone (should not be returned)
        $event3 = $this->create_event_without_timezone();
        update_post_meta($event3, '_EventTimezone', 'America/New_York');

        // Get IDs of events needing update
        $ids = $this->updater->get_ids();

        // Should return exactly two events
        $this->assertCount(2, $ids, 'Get IDs should return 2 events when 2 events lack time zone data');
        $this->assertContains($event1, $ids, 'Get IDs should contain event1');
        $this->assertContains($event2, $ids, 'Get IDs should contain event2');
        $this->assertNotContains($event3, $ids, 'Get IDs should not contain event3');
    }

    public function test_process_should_update_events_with_timezone_data(): void {
        // Create a test event
        $event_id = $this->create_event_without_timezone();

        // Process updates
        $this->updater->process();

        // Verify time zone data was added
        $this->assertNotEmpty(get_post_meta($event_id, '_EventTimezone', true), 'Event time zone should be set');
        $this->assertNotEmpty(get_post_meta($event_id, '_EventTimezoneAbbr', true), 'Event time zone abbreviation should be set');
        $this->assertNotEmpty(get_post_meta($event_id, '_EventStartDateUTC', true), 'Event start date UTC should be set');
        $this->assertNotEmpty(get_post_meta($event_id, '_EventEndDateUTC', true), 'Event end date UTC should be set');
    }

    public function test_process_should_respect_batch_size(): void {
        // Create more events than the batch size
        for ($i = 0; $i < 5; $i++) {
            $this->create_event_without_timezone();
        }

        // Process only 2 events
        $this->updater->process(2);

        // Should still have 3 events needing updates
        $this->assertEquals(3, $this->updater->count_ids(), 'Count IDs should return 3 when 3 events lack time zone data');
    }

    public function test_init_update_should_process_initial_batch(): void {
        // Create test events
        for ($i = 0; $i < 3; $i++) {
            $this->create_event_without_timezone();
        }

        // The initial count should be 3
        $this->assertEquals(3, $this->updater->count_ids(), 'Initial count should be 3 when 3 events lack time zone data');

        // Run init_update
        $this->updater->init_update();

        // Should have processed all events (default batch size is 50)
        $this->assertEquals(0, $this->updater->count_ids(), 'Count IDs should be 0 when all events have been processed');
    }
}
