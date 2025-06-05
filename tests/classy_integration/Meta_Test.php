<?php
/**
 *
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Tests\Events\Classy;

use TEC\Common\StellarWP\DB\DB;
use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Classy\Meta;
use Tribe__Events__Main as TEC;

/**
 * Class Meta_Test
 *
 * @since TBD
 */
class Meta_Test extends Controller_Test_Case {
	protected $controller_class = Meta::class;

	public function test_on_rest_insert_event_does_not_add_meta_to_non_event_post():void{
		$post = static::factory()->post->create_and_get(  );
		// Sanity check.
		$this->assertEquals('', get_post_meta($post->ID, '_EventStartDateUTC',true));
		$this->assertEquals('', get_post_meta($post->ID, '_EventEndDateUTC',true));

		$this->make_controller()->register();
		apply_filters( 'rest_after_insert_' . TEC::POSTTYPE, $post );

		$this->assertEquals('', get_post_meta($post->ID, '_EventStartDateUTC',true));
		$this->assertEquals('', get_post_meta($post->ID, '_EventEndDateUTC',true));
	}

	public function test_on_rest_insert_event_adds_meta_to_event_post():void{
		// Create the post with a post type that will not trigger any event-related filter or action.
		$pseudo_event = static::factory()->post->create_and_get(  ['post_type'=>'pseudo_event']);
		// Update the date post meta as the REST API save would.
		update_post_meta($pseudo_event->ID, '_EventStartDate', '2020-01-01 10:00:00');
		update_post_meta($pseudo_event->ID, '_EventEndDate', '2020-01-01 13:00:00');
		update_post_meta($pseudo_event->ID, '_EventTimezone', 'Europe/Paris');
		// Update the event post type with a direct db call to avoid triggering any event-related filter or action.
		global $wpdb;
		DB::query( DB::prepare( "UPDATE %i SET post_type = %s WHERE ID = %d", $wpdb->posts, TEC::POSTTYPE, $pseudo_event->ID ) );
		// Re-fetch the event.
		clean_post_cache( $pseudo_event->ID );
		$event = get_post( $pseudo_event->ID );
		// Sanity check.
		$this->assertEquals('', get_post_meta($event->ID, '_EventStartDateUTC',true));
		$this->assertEquals('', get_post_meta($event->ID, '_EventEndDateUTC',true));

		$this->make_controller()->register();
		apply_filters( 'rest_after_insert_' . TEC::POSTTYPE, $event );

		// Sanity check.
		$this->assertEquals('2020-01-01 09:00:00', get_post_meta($event->ID, '_EventStartDateUTC',true));
		$this->assertEquals('2020-01-01 12:00:00', get_post_meta($event->ID, '_EventEndDateUTC',true));
	}

	public function test_on_rest_insert_events_does_not_change_meta_if_present():void{
		// Create the post with a post type that will not trigger any event-related filter or action.
		$pseudo_event = static::factory()->post->create_and_get(  ['post_type'=>'pseudo_event']);
		// Update the date post meta as the REST API save would.
		update_post_meta($pseudo_event->ID, '_EventStartDate', '2020-01-01 10:00:00');
		update_post_meta($pseudo_event->ID, '_EventEndDate', '2020-01-01 13:00:00');
		update_post_meta($pseudo_event->ID, '_EventTimezone', 'Europe/Paris');
		// Set the UTC start and end date and time to very distinguishable strings.
		update_post_meta($pseudo_event->ID, '_EventStartDateUTC', 'You cannot pass!');
		update_post_meta($pseudo_event->ID, '_EventEndDateUTC', 'not a UTC time');
		// Update the event post type with a direct db call to avoid triggering any event-related filter or action.
		global $wpdb;
		DB::query( DB::prepare( "UPDATE %i SET post_type = %s WHERE ID = %d", $wpdb->posts, TEC::POSTTYPE, $pseudo_event->ID ) );
		// Re-fetch the event.
		clean_post_cache( $pseudo_event->ID );
		$event = get_post( $pseudo_event->ID );

		$this->make_controller()->register();
		apply_filters( 'rest_after_insert_' . TEC::POSTTYPE, $event );

		// Sanity check.
		$this->assertEquals('You cannot pass!', get_post_meta($event->ID, '_EventStartDateUTC',true));
		$this->assertEquals('not a UTC time', get_post_meta($event->ID, '_EventEndDateUTC',true));
	}
}
