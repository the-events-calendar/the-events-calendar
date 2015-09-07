<?php
namespace Tribe\Events\Test\UITester;

/**
 * Event Management
 */
class EventSteps extends \Tribe\Events\Test\UITester
{

	/**
	 * Default Event meta
	 */
	private static $defaultEvent = array (
		'title' => "My Event",
		'content' => "Automated Event",
		'allDay' => false
	);

	/**
	 * Create new Event
	 */
    public function createEvent( $event = null)
    {
		if( is_null( $event ) ) {
			$event = $this->generateEvent();
		} else {
			$event = array_merge( self::$defaultEvent, $event);
		}

        $I = $this;
		$I->amOnPage('wp-admin/post-new.php?post_type=tribe_events');
		$I->fillField('post_title', $event['title'] );
		//$I->fillField('content', $event['content'] ); // need to target WYSIWYG instance
		if( $event['allDay'] ) {
			$I->checkOption('#allDayCheckbox');
		}
		$I->click('#publish');
		$I->see('Event published');

		// TODO Full Valdation of Event Properties based of passed flag
    }

	/**
	 * Delete Event
	 */
	public function deleteEvent()
    {
        $I = $this;
    }

	/**
	 * Generate random event meta
	 */
	public function generateEvent()
	{
		$random = array ();
		$random['title'] = "Event " + time();
		$random['content'] = "Description " + time();
		return array_merge( self::$defaultEvent, $random );
	}
}