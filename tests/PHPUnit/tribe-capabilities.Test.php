<?php

/**
 * Tests tribe capabilities assignations to make sure they are correct.
 *
 * @package TribeEvents
 * @since 2.0.5
 * @author Paul Hughes
 */
class WP_Test_TribeCapabilities extends Tribe_WP_UnitTestCase {
	
	/**
	 * Add to the setUp() function the initialization of the plugin, when assignations take place.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */
	public function setUp() {
		parent::setUp();
		$tribe_ecp = TribeEvents::instance();
		$tribe_ecp->init();		
	}
	
	/**
	 * Test to make sure the administrator role has all the capabilities related to tribe events.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */	
	public function test_administrator_role_capabilities() {
	
		$role = get_role('administrator');
		
		$this->assertInstanceOf( 'WP_Role', $role );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_events') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_events' ) );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venues') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_venues' ) );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizers') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_organizers' ) );
	}
	
	/**
	 * Test to make sure the editor role has all the capabilities related to tribe events.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */	
	public function test_editor_role_capabilities() {
	
		$role = get_role('editor');
		
		$this->assertInstanceOf( 'WP_Role', $role );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_events') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_events' ) );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venues') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_venues' ) );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizers') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'read_private_tribe_organizers' ) );
	}
	
	/**
	 * Test to make sure the author role has the proper capabilities related to tribe events.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */	
	public function test_author_role_capabilities() {
	
		$role = get_role('author');
		
		$this->assertInstanceOf( 'WP_Role', $role );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_events') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_events' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );
				
		$this->assertTrue( $role->has_cap( 'edit_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venues') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_venues' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_venues' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_venues' ) );
				
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizers') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'publish_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_tribe_organizers' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_organizers' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_organizers' ) );
	}
	
	/**
	 * Test to make sure the contributor role has the proper capabilities related to tribe events.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */	
	public function test_contributor_role_capabilities() {
	
		$role = get_role('contributor');
		
		$this->assertInstanceOf( 'WP_Role', $role );
		
		$this->assertTrue( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_events') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_events' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );
				
		$this->assertTrue( $role->has_cap( 'edit_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venue' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_venues') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_venues' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_venues' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_venues' ) );
						
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'read_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizer' ) );
		$this->assertTrue( $role->has_cap( 'delete_tribe_organizers') );
		$this->assertTrue( $role->has_cap( 'edit_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_organizers' ) );		
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_organizers' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_organizers' ) );
	}
	
	/**
	 * Test to make sure the subscriber role has the proper capabilities related to tribe events.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */		
	public function test_subscriber_role_capabilities() {
	
		$role = get_role('subscriber');
		
		$this->assertInstanceOf( 'WP_Role', $role );
		
		$this->assertTrue( $role->has_cap( 'read_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_events') );
		$this->assertFalse( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );
				
		$this->assertTrue( $role->has_cap( 'read_tribe_venue' ) );
		$this->assertFalse( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_events') );
		$this->assertFalse( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );
		
		$this->assertTrue( $role->has_cap( 'read_tribe_organizer' ) );
		$this->assertFalse( $role->has_cap( 'edit_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_event' ) );
		$this->assertFalse( $role->has_cap( 'delete_tribe_events') );
		$this->assertFalse( $role->has_cap( 'edit_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_others_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'publish_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_published_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'delete_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'edit_private_tribe_events' ) );
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );
	}
	
}