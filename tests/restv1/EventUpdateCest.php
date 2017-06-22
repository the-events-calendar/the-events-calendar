<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class EventUpdateCest extends BaseRestCest
{
    /**
     * It should return 403 if user cannot update events
     *
     * @test
     */
    public function it_should_return_403_if_user_cannot_update_events(Tester $I)
    {
        $event_id = $I->haveEventInDatabase();

        $I->sendPOST($this->events_url . "/{$event_id}", [
            'title' => 'Updated title',
        ]);

        $response = $I->grabResponse();
        codecept_debug($response);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }
}
