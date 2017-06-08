<?php


namespace Tribe\Events\Aggregator\Record;

use Tribe\Events\Tests\Factories\REST\V1\Event_Response;
use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Aggregator__Record__Url as Url;
use Tribe__Events__Main as TEC;

class UrlTest extends Events_TestCase {

	function setUp() {
		parent::setUp();
		$this->factory()->event_response = new Event_Response();
		\Tribe__Image__Uploader::reset_cache();
	}

	/**
	 * It should be instantiatable
	 *
	 * @test
	 */
	public function be_instantiatable() {
		$this->assertInstanceOf( Url::class, $this->make_instance() );
	}

	/**
	 * @return Url
	 */
	protected function make_instance() {
		return new Url();
	}

	/**
	 * It should not re-import image by URL when set in event information
	 *
	 * @test
	 */
	public function it_should_not_reimport_image_by_url_when_set_in_event_information() {
		$dummy_event = $this->factory()->event_response->create_and_get();
		$event = $this->convert_rest_event_data_to_ea_format( $dummy_event );
		$dummy_event_id = $event['id'];
		unset( $event['id'] );
		$event['title'] = md5( $event['title'] );
		$image = codecept_data_dir( 'images/featured-image.jpg' );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image );
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$event['image'] = $attachment_url;

		$sut = $this->make_instance();
		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $sut->insert_posts( [ $event ] );

		$items = $activity->get( TEC::POSTTYPE );
		$inserted_event_id = $items->created[0];

		$this->assertNotEquals( $dummy_event_id, $inserted_event_id );
		$thumbnail_id = get_post_thumbnail_id( $inserted_event_id );
		$this->assertEquals( $attachment_id, $thumbnail_id );
	}

	/**
	 * It should not reimport the image when importing the same event many times
	 *
	 * @test
	 */
	public function it_should_not_reimport_the_image_when_importing_the_same_event_many_times() {
		$dummy_event = $this->factory()->event_response->create_and_get();
		$event = $this->convert_rest_event_data_to_ea_format( $dummy_event );
		unset( $event['id'] );
		$event['title'] = md5( $event['title'] );
		$image = codecept_data_dir( 'images/featured-image.jpg' );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image );
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$event['image'] = $attachment_url;

		$sut = $this->make_instance();
		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $sut->insert_posts( [ $event ] );

		$items = $activity->get( TEC::POSTTYPE );
		$inserted_event_id = $items->created[0];

		// force the same event to be reimported
		$event['ID'] = $inserted_event_id;

		for ( $i = 0; $i < 5; $i ++ ) {
			// change the title to trigger an update of the event
			$event['title'] = $event['title'] . $i;

			$record = $this->make_instance();
			$record->insert_posts( [ $event ] );

			/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
			$activity = $sut->insert_posts( [ $event ] );

			$items = $activity->get( TEC::POSTTYPE );
			$inserted_event_id = $items->created[0];

			$thumbnail_id = get_post_thumbnail_id( $inserted_event_id );
			$this->assertEquals( $attachment_id, $thumbnail_id );
		}
	}

	/**
	 * It should not reimport image when attached to different events
	 *
	 * @test
	 */
	public function it_should_not_reimport_image_when_attached_to_different_events() {
		$dummy_event = $this->factory()->event_response->create_and_get();
		$event = $this->convert_rest_event_data_to_ea_format( $dummy_event );
		unset( $event['id'] );
		$event['title'] = md5( $event['title'] );
		$image = codecept_data_dir( 'images/featured-image.jpg' );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image );
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$event['image'] = $attachment_url;

		$sut = $this->make_instance();
		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $sut->insert_posts( [ $event ] );

		$items = $activity->get( TEC::POSTTYPE );
		$inserted_event_id = $items->created[0];

		for ( $i = 0; $i < 5; $i ++ ) {
			// change the title to make sure this event counts as new
			$event['title'] = $event['title'] . $i;

			$record = $this->make_instance();
			$record->insert_posts( [ $event ] );

			/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
			$activity = $sut->insert_posts( [ $event ] );

			$items = $activity->get( TEC::POSTTYPE );
			$inserted_event_id = $items->created[0];

			$thumbnail_id = get_post_thumbnail_id( $inserted_event_id );
			$this->assertEquals( $attachment_id, $thumbnail_id );
		}
	}
}