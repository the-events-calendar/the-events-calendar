<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class SettingsTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * @before
	 * @after
	 */
	public function reset_settings_state(): void {
		tribe_remove_option( Settings::LOCK_OPTION );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		tribe( Pro_History::class )->reset();
		remove_all_filters( 'tec_events_recurrence_pro_rules_locked' );
		remove_all_filters( 'tec_events_recurrence_can_convert' );
		remove_all_filters( 'tec_should_hide_upsell' );
	}

	/**
	 * It should link to events calendar pro unless upsells are hidden
	 *
	 * @test
	 */
	public function should_link_to_events_calendar_pro_unless_upsells_are_hidden(): void {
		if ( defined( 'TEC_HIDE_UPSELL' ) || defined( 'TRIBE_HIDE_UPSELL' ) || false !== getenv( 'TEC_HIDE_UPSELL' ) ) {
			$this->markTestSkipped( 'Upsells are hidden by the environment.' );
		}

		tribe( Pro_History::class )->mark_detected( 'test' );

		$fields = apply_filters( 'tribe_general_settings_editing_section', [] );
		$this->assertStringContainsString( 'href="' . Settings::PRO_URL . '"', $fields[ Settings::LOCK_OPTION ]['tooltip'] );
		$this->assertStringContainsString( 'target="_blank" rel="noopener noreferrer"', $fields[ Settings::LOCK_OPTION ]['tooltip'] );
		$this->assertStringContainsString( 'Get Events Calendar Pro</a>', $fields[ Settings::LOCK_OPTION ]['tooltip'] );

		add_filter( 'tec_should_hide_upsell', '__return_true' );
		$fields = apply_filters( 'tribe_general_settings_editing_section', [] );
		$this->assertStringNotContainsString( '<a ', $fields[ Settings::LOCK_OPTION ]['tooltip'] );
		$this->assertStringContainsString( 'will not restore them.', $fields[ Settings::LOCK_OPTION ]['tooltip'] );
	}

	private function given_a_rule_locked_event(): WP_Post {
		$post = $this->given_a_multi_date_event();
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART;TZID=UTC:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=3" ] );

		return $post;
	}

	/**
	 * It should register as a controller sub provider
	 *
	 * @test
	 */
	public function should_register_as_a_controller_sub_provider(): void {
		$settings = tribe( Settings::class );

		$this->assertEquals( 10, has_filter( 'tribe_general_settings_editing_section', [ $settings, 'add_fields' ] ) );
		$this->assertEquals( 10, has_action( 'tec_events_recurrence_rules_frozen', [ $settings, 'mark_pro_history' ] ) );

		tribe( Controller::class )->unregister();

		$this->assertFalse( has_filter( 'tribe_general_settings_editing_section', [ $settings, 'add_fields' ] ) );
		$this->assertFalse( has_action( 'tec_events_recurrence_rules_frozen', [ $settings, 'mark_pro_history' ] ) );

		tribe( Controller::class )->register();

		$this->assertEquals( 10, has_filter( 'tribe_general_settings_editing_section', [ $settings, 'add_fields' ] ) );
	}

	/**
	 * It should default the lock to enabled
	 *
	 * @test
	 */
	public function should_default_the_lock_to_enabled(): void {
		$this->assertTrue( tribe( Settings::class )->is_lock_enabled() );
	}

	/**
	 * It should read the saved option
	 *
	 * @test
	 */
	public function should_read_the_saved_option(): void {
		tribe_update_option( Settings::LOCK_OPTION, false );
		$this->assertFalse( tribe( Settings::class )->is_lock_enabled() );

		tribe_update_option( Settings::LOCK_OPTION, true );
		$this->assertTrue( tribe( Settings::class )->is_lock_enabled() );
	}

	/**
	 * It should honor the lock filter
	 *
	 * @test
	 */
	public function should_honor_the_lock_filter(): void {
		add_filter( 'tec_events_recurrence_pro_rules_locked', '__return_false' );

		$this->assertFalse( tribe( Settings::class )->is_lock_enabled() );
	}

	/**
	 * It should add the fields only with pro history
	 *
	 * @test
	 */
	public function should_add_the_fields_only_with_pro_history(): void {
		$fields = apply_filters( 'tribe_general_settings_editing_section', [ 'existing' => [ 'type' => 'html' ] ] );

		// Other providers (the blocks editor toggle) filter the same section: look at our key only.
		$this->assertArrayHasKey( 'existing', $fields );
		$this->assertArrayNotHasKey( Settings::LOCK_OPTION, $fields, 'Without Pro history the field is not added at all.' );

		tribe( Pro_History::class )->mark_detected( 'test' );
		$fields = apply_filters( 'tribe_general_settings_editing_section', [ 'existing' => [ 'type' => 'html' ] ] );

		// One more checkbox appended to the section, laid out like the ones before it: no header of its own.
		$this->assertEquals( Settings::LOCK_OPTION, array_key_last( $fields ) );
		$this->assertCount( 1, preg_grep( '/recurrence/', array_keys( $fields ) ) );
		$this->assertTrue( $fields[ Settings::LOCK_OPTION ]['default'] );
		$this->assertEquals( 'checkbox_bool', $fields[ Settings::LOCK_OPTION ]['type'] );
		$this->assertEquals( Settings::FIELD_ID, $fields[ Settings::LOCK_OPTION ]['attributes']['id'] );
		$this->assertNotEmpty( $fields[ Settings::LOCK_OPTION ]['label'] );
		$this->assertNotEmpty( $fields[ Settings::LOCK_OPTION ]['tooltip'] );
	}

	/**
	 * It should allow conversion only for locked events with the lock off
	 *
	 * @test
	 */
	public function should_allow_conversion_only_for_locked_events_with_the_lock_off(): void {
		$settings = tribe( Settings::class );
		$locked   = $this->given_a_rule_locked_event();
		$dates    = $this->given_a_multi_date_event();

		// Lock on (default): nothing converts.
		$this->assertFalse( $settings->can_convert( $locked->ID ) );
		$this->assertFalse( $settings->can_convert( $dates->ID ) );

		tribe_update_option( Settings::LOCK_OPTION, false );

		$this->assertTrue( $settings->can_convert( $locked->ID ) );
		$this->assertFalse( $settings->can_convert( $dates->ID ), 'A dates-only Event has nothing to convert.' );
		$this->assertFalse( $settings->can_convert( 0 ) );

		add_filter( 'tec_events_recurrence_can_convert', '__return_false' );
		$this->assertFalse( $settings->can_convert( $locked->ID ) );
	}

	/**
	 * It should build the settings url to the field
	 *
	 * @test
	 */
	public function should_build_the_settings_url_to_the_field(): void {
		$url = tribe( Settings::class )->get_settings_url();

		$this->assertStringContainsString( 'page=tec-events-settings', $url );
		$this->assertStringContainsString( 'tab=' . Settings::TAB_SLUG, $url );
		$this->assertStringEndsWith( '#' . Settings::FIELD_ID, $url );
	}
}
