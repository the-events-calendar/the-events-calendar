<?php

/**
 * Class Tribe__Events__Pro__Updates__Recurrence_Meta_To_Child_Post_Converter_Test
 *
 * @group recurrence
 * @group updates
 */
class Tribe__Events__Pro__Updates__Recurrence_Meta_To_Child_Post_Converter_Test extends Tribe__Events__WP_UnitTestCase {
	public function test_update_3_5() {
		tribe_update_option( 'pro-schema-version', '3.4' );
		$event_id = wp_insert_post(array(
			'post_title' => __CLASS__,
			'post_content' => __FUNCTION__,
			'post_status' => 'publish',
			'post_type' => TribeEvents::POSTTYPE,
		));
		add_post_meta($event_id, '_EventStartDate', '2014-05-01 16:00:00');
		add_post_meta($event_id, '_EventStartDate', '2014-05-08 16:00:00');
		add_post_meta($event_id, '_EventStartDate', '2014-05-15 16:00:00');
		add_post_meta($event_id, '_EventStartDate', '2014-05-22 16:00:00');
		add_post_meta($event_id, '_EventStartDate', '2014-05-29 16:00:00');
		add_post_meta($event_id, '_EventEndDate', '2014-05-01 17:00:00');
		add_post_meta($event_id, '_EventDuration', '3600');
		add_post_meta($event_id, '_EventRecurrence', array(
			'type' => 'Every Week',
			'end-type' => 'After',
			'end' => '',
			'end-count' => '5',
			'custom-type' => 'Daily',
			'custom-interval' => '',
			'custom-type-text' => '',
			'occurrence-count-text' => 'weeks',
			'custom-month-number' => 'First',
			'custom-month-day' => '1',
			'custom-year-month-number' => '1',
			'custom-year-month-day' => '1',
			'recurrence-description' => '',
			'EventStartDate' => '2014-05-01 16:00:00',
			'EventEndDate' => '2014-05-01 17:00:00',
		));

		require_once( TribeEventsPro::instance()->pluginPath . '/lib/Updater.php' );
		$updater = new Tribe__Events__Pro__UPdater( '3.5' );
		$updater->do_updates();

		$this->assertCount( 1, get_post_meta($event_id, '_EventStartDate', false) );
		$this->assertEquals( '2014-05-01 16:00:00', get_post_meta( $event_id, '_EventStartDate', true) );
		$this->assertEquals( '2014-05-01 17:00:00', get_post_meta( $event_id, '_EventEndDate', true) );

		$children = get_posts(array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_parent' => $event_id,
			'post_status' => 'publish',
			'meta_key' => '_EventStartDate',
			'orderby' => 'meta_value',
			'order' => 'ASC',
			'fields' => 'ids',
		));

		$this->assertCount( 4, $children );
		$this->assertCount( 1, get_post_meta( $children[0], '_EventStartDate', false ) );

		$this->assertEquals( '2014-05-08 16:00:00', get_post_meta( $children[0], '_EventStartDate', true ) );
		$this->assertEquals( '2014-05-15 16:00:00', get_post_meta( $children[1], '_EventStartDate', true ) );
		$this->assertEquals( '2014-05-22 16:00:00', get_post_meta( $children[2], '_EventStartDate', true ) );
		$this->assertEquals( '2014-05-29 16:00:00', get_post_meta( $children[3], '_EventStartDate', true ) );

		$this->assertEquals( '2014-05-08 17:00:00', get_post_meta( $children[0], '_EventEndDate', true ) );
		$this->assertEquals( '2014-05-15 17:00:00', get_post_meta( $children[1], '_EventEndDate', true ) );
		$this->assertEquals( '2014-05-22 17:00:00', get_post_meta( $children[2], '_EventEndDate', true ) );
		$this->assertEquals( '2014-05-29 17:00:00', get_post_meta( $children[3], '_EventEndDate', true ) );

		$this->assertEquals( '3600', get_post_meta( $children[0], '_EventDuration', true ) );
		$this->assertEquals( '3600', get_post_meta( $children[1], '_EventDuration', true ) );
		$this->assertEquals( '3600', get_post_meta( $children[2], '_EventDuration', true ) );
		$this->assertEquals( '3600', get_post_meta( $children[3], '_EventDuration', true ) );
	}
}
 