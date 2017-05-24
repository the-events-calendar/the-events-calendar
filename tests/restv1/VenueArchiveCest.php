<?php

use Step\Restv1\RestGuy as Tester;

class VenueArchiveCest
{
	/**
	 * It should return bad request if trying to get events by non numeric venue
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_events_by_non_numeric_venue(Tester $I) {
	}

	/**
	 * It should retuen bad request if trying to get events by non existing venue ID
	 *
	 * @test
	 */
	public function it_should_retuen_bad_request_if_trying_to_get_events_by_non_existing_venue_id() {
	}

	/**
	 * It should return 404 if trying to get events by venue not assigned to any event
	 *
	 * @test
	 */
	public function it_should_return_404_if_trying_to_get_events_by_venue_not_assigned_to_any_event() {
	}

	/**
	 * It should return events related to the venue when specifying existing venue ID
	 *
	 * @test
	 */
	public function it_should_return_events_related_to_the_venue_when_specifying_existing_venue_id() {
	}

	/**
	 * It should not return non public events related to existing venue ID
	 *
	 * @test
	 */
	public function it_should_not_return_non_public_events_related_to_existing_venue_id() {
	}

	/**
	 * It should return non public events related to existing venue ID if user can edit events
	 *
	 * @test
	 */
	public function it_should_return_non_public_events_related_to_existing_venue_id_if_user_can_edit_events() {
	}

}
