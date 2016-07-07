<?php

namespace tests\functional\Tribe\Events\Importer;

require_once 'File_Importer_EventsTest.php';

use Tribe\Events\Importer\File_Importer_EventsTest;

class File_Importer_Events_ReimportBehaviourTest extends File_Importer_EventsTest {

	/**
	 * @var string
	 */
	protected $template = 'events';

	/**
	 * @var string
	 */
	protected $featured_image_url;

	/**
	 * @var array
	 */
	protected $data = [
		'title'          => 'Some event',
		'venue_name'     => 'Venue',
		'organizer_name' => 'Organizer',
		'start_date'     => 'apr 19, 2016',
		'start_time'     => '12:30',
		'end_date'       => 'apr 19, 2016',
		'end_time'       => '13:30',
		'all_day'        => 'false',
		'categories'     => 'service',
		'cost'           => '10',
		'website'        => 'http://example.com',
		'show_map'       => 'true',
		'show_map_link'  => 'true',
		'description'    => 'Some event description',
		'featured_image' => '', // filled during setUp method
	];

	protected $field_map = [
		'event_name',
		'event_venue_name',
		'event_organizer_name',
		'event_start_date',
		'event_start_time',
		'event_end_date',
		'event_end_time',
		'event_all_day',
		'event_category',
		'event_cost',
		'event_website',
		'event_show_map',
		'event_show_map_link',
		'event_description',
		'featured_image',
	];

	/**
	 * These fields will not be used by the importer to uniquely identify the event.
	 *
	 * @var array
	 */
	protected $secondary_fields = [
		'venue_name',
		'organizer_name',
		'end_date',
		'end_time',
		'categories',
		'cost',
		'website',
		'show_map',
		'show_map_link',
		'description',
		'featured_image',
	];

	public function setUp() {
		parent::setUp();
		$this->featured_image_url     = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$this->data['featured_image'] = $this->featured_image_url;
	}

	/**
	 * @test
	 * it should not remove featured image when re-importing same event from file
	 */
	public function it_should_not_remove_featured_image_when_re_importing_same_event_from_file() {
		$image_url     = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image_url );
		$this->featured_image_uploader->upload_and_get_attachment()->willReturn( $attachment_id );

		$sut = $this->make_instance( 'reimport-behaviour' );

		$first_post_id = $sut->import_next_row();

		$first_featured_image_id  = get_post_thumbnail_id( $first_post_id );
		$first_featured_image_url = wp_get_attachment_url( $first_featured_image_id );

		clean_post_cache( $first_post_id );
		clean_attachment_cache( $first_featured_image_id );

		$sut = $this->make_instance( 'reimport-behaviour' );

		$second_post_id = $sut->import_next_row();

		$second_featured_image_id  = get_post_thumbnail_id( $second_post_id );
		$second_featured_image_url = wp_get_attachment_url( $second_featured_image_id );

		$this->assertEquals( $first_post_id, $second_post_id );
		$this->assertEquals( $first_featured_image_id, $second_featured_image_id );
		$this->assertEquals( $first_featured_image_url, $second_featured_image_url );
	}

	public function single_secondary_field() {
		return array_map( function ( $field ) {
			return [ $field ];
		}, $this->secondary_fields );
	}

	/**
	 * @test
	 * it should not modify the featured image when re-importing file with empty secondary fields
	 * @dataProvider single_secondary_field
	 */
	public function it_should_not_modify_the_featured_image_when_re_importing_file_with_empty_secondary_fields( $field ) {
		$image_url     = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image_url );
		$this->featured_image_uploader->upload_and_get_attachment()->willReturn( $attachment_id );

		$sut = $this->make_instance( 'reimport-behaviour' );

		$first_post_id = $sut->import_next_row();

		$first_featured_image_id  = get_post_thumbnail_id( $first_post_id );
		$first_featured_image_url = wp_get_attachment_url( $first_featured_image_id );

		clean_post_cache( $first_post_id );
		clean_attachment_cache( $first_featured_image_id );

		$this->data[ $field ] = '';

		$sut = $this->make_instance( 'reimport-behaviour' );

		$second_post_id = $sut->import_next_row();

		$second_featured_image_id  = get_post_thumbnail_id( $second_post_id );
		$second_featured_image_url = wp_get_attachment_url( $second_featured_image_id );

		$this->assertEquals( $first_post_id, $second_post_id );
		$this->assertEquals( $first_featured_image_id, $second_featured_image_id );
		$this->assertEquals( $first_featured_image_url, $second_featured_image_url );
	}

	public function modified_secondary_fields() {
		return [
			[ 'venue_name', 'Another Venue' ],
			[ 'organizer_name', 'Another Organizer' ],
			[ 'end_date', 'apr 21, 2016' ],
			[ 'end_time', '16:30' ],
			[ 'categories', 'gathering' ],
			[ 'cost', '2' ],
			[ 'website', 'http://some-example.com' ],
			[ 'show_map', 'false' ],
			[ 'show_map_link', 'false' ],
			[ 'description', 'Another event description' ],
		];
	}

	/**
	 * @test
	 * it should not modify the featured image when re-importing file with modified secondary fields
	 * @dataProvider modified_secondary_fields
	 */
	public function it_should_not_modify_the_featured_image_when_re_importing_file_with_modified_secondary_fields( $field, $modified_value ) {
		$image_url     = plugins_url( '_data/csv-import-test-files/featured-image/images/featured-image.jpg', codecept_data_dir() );
		$attachment_id = $this->factory()->attachment->create_upload_object( $image_url );
		$this->featured_image_uploader->upload_and_get_attachment()->willReturn( $attachment_id );

		$sut = $this->make_instance( 'reimport-behaviour' );

		$first_post_id = $sut->import_next_row();

		$first_featured_image_id  = get_post_thumbnail_id( $first_post_id );
		$first_featured_image_url = wp_get_attachment_url( $first_featured_image_id );

		clean_post_cache( $first_post_id );
		clean_attachment_cache( $first_featured_image_id );

		$this->data[ $field ] = $modified_value;

		$sut = $this->make_instance( 'reimport-behaviour' );

		$second_post_id = $sut->import_next_row();

		$second_featured_image_id  = get_post_thumbnail_id( $second_post_id );
		$second_featured_image_url = wp_get_attachment_url( $second_featured_image_id );

		$this->assertEquals( $first_post_id, $second_post_id );
		$this->assertEquals( $first_featured_image_id, $second_featured_image_id );
		$this->assertEquals( $first_featured_image_url, $second_featured_image_url );
	}

}