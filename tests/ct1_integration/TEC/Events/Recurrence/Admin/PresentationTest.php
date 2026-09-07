<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use TEC\Events\Recurrence\Pro_History;
use Tribe\Tests\Traits\With_Uopz;

class PresentationTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;

	/** @after */
	public function drop_temporary_series_table(): void {
		global $wpdb;
		$table = Pro_History::series_relationships_table();
		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS $table" );
	}

	/** @test */
	public function should_distinguish_schedule_from_row_identity(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', $post->ID )->order_by( 'start_date', 'ASC' )->first();
		$presentation = new Presentation();
		$data = $presentation->get( $row->provisional_id );
		$this->assertSame( 'dates', $data['schedule'] );
		$this->assertTrue( $data['isOccurrence'] );
		$this->assertFalse( $data['locked'] );
		$this->assertSame( 2, $data['count'] );
		$this->assertSame( $post->ID, $data['eventId'] );
		$this->assertStringContainsString( 'post=' . $post->ID . '&action=edit', $data['parentEditLink'] );
		$this->assertStringContainsString( '2050', $data['start'] );
		$this->assertStringContainsString( 'UTC', $data['start'] );
		$this->assertFalse( $presentation->get( $post->ID )['isOccurrence'] );
	}

	/** @test */
	public function should_identify_rules_even_when_only_one_date_remains(): void {
		$post = $this->given_a_multi_date_event();
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=2" ] );
		$rows = iterator_to_array( Occurrence::where( 'post_id', $post->ID )->all(), false );
		$rows[1]->delete();
		$data = ( new Presentation() )->get( $rows[0]->provisional_id );
		$this->assertSame( 'rules', $data['schedule'] );
		$this->assertSame( 1, $data['count'] );
		$this->assertTrue( $data['locked'] );
	}

	/** @test */
	public function should_identify_single_events_and_unscheduled_drafts(): void {
		$post = tribe_events()->set_args( [ 'title' => 'Recurring in title only', 'status' => 'publish', 'start_date' => '2050-02-01 09:00:00', 'end_date' => '2050-02-01 10:00:00' ] )->create();
		$this->assertSame( 'single', ( new Presentation() )->get( $post->ID )['schedule'] );
		$draft = static::factory()->post->create( [ 'post_type' => 'tribe_events', 'post_status' => 'draft' ] );
		$data = ( new Presentation() )->get( $draft );
		$this->assertFalse( $data['isOccurrence'] );
		$this->assertSame( 'Not scheduled', $data['start'] );
		$this->assertFalse( $data['scheduled'] );
	}

	/** @test */
	public function should_batch_preserved_series_and_withhold_unreadable_names(): void {
		global $wpdb;
		$table = Pro_History::series_relationships_table();
		// WPTestCase makes this temporary; it shadows any table left by earlier tests.
		$wpdb->query( "CREATE TABLE $table (event_post_id bigint unsigned, series_post_id bigint unsigned)" );
		$this->set_class_fn_return( Pro_History::class, 'series_relationships_table_exists', true );
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '1.0.0' );
		$admin = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin );
		$post = $this->given_a_multi_date_event();
		$other = $this->given_a_multi_date_event();
		$series = static::factory()->post->create( [ 'post_type' => Pro_History::SERIES_POST_TYPE, 'post_status' => 'publish', 'post_title' => 'Public Series' ] );
		$private = static::factory()->post->create( [ 'post_type' => Pro_History::SERIES_POST_TYPE, 'post_status' => 'private', 'post_title' => 'Private Series', 'post_author' => $admin ] );
		$wrong = static::factory()->post->create( [ 'post_type' => 'post' ] );
		foreach ( [ $series, $private, $wrong, 99999999 ] as $id ) {
			$wpdb->insert( $table, [ 'event_post_id' => $post->ID, 'series_post_id' => $id ] );
		}
		$wpdb->insert( $table, [ 'event_post_id' => $other->ID, 'series_post_id' => $series ] );
		$presentation = new Presentation();
		$presentation->prime( [ $post, $other ] );
		$this->assertCount( 2, $presentation->get( $post->ID )['series'] );
		$occurrence = Occurrence::where( 'post_id', $post->ID )->first();
		$this->assertSame( $presentation->get( $post->ID )['series'], $presentation->get( $occurrence->provisional_id )['series'] );
		// Removing the relationship after priming proves both parents were loaded together.
		$wpdb->delete( $table, [ 'event_post_id' => $other->ID ] );
		$this->assertCount( 1, $presentation->get( $other->ID )['series'] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->assertSame( [ [ 'id' => $series, 'title' => 'Public Series' ] ], $presentation->get( $post->ID )['series'] );
		wp_set_current_user( 0 );
		$this->assertSame( [], $presentation->get( $post->ID )['series'] );
		$presentation->reset();
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '2.0.0' );
		$this->assertSame( [], $presentation->get( $post->ID )['series'] );
	}

	/** @test */
	public function should_skip_missing_or_incomplete_series_tables(): void {
		global $wpdb;
		$table = Pro_History::series_relationships_table();
		$wpdb->query( "CREATE TABLE $table (event_post_id bigint unsigned)" );
		update_option( Pro_History::SERIES_SCHEMA_OPTION, '1.0.0' );
		$this->set_class_fn_return( Pro_History::class, 'series_relationships_table_exists', false );
		$post = $this->given_a_multi_date_event();
		$this->assertSame( [], ( new Presentation() )->get( $post->ID )['series'] );
		$this->set_class_fn_return( Pro_History::class, 'series_relationships_table_exists', true );
		$this->assertSame( [], ( new Presentation() )->get( $post->ID )['series'] );
		$this->assertSame( '', $wpdb->last_error );
	}

}
