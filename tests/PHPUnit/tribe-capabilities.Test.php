<?php
/* -- tribe-capabilities.Test.php -- */

class WP_Test_TribeCapabilities extends Tribe_WP_UnitTestCase {
	
	public function setUp() {
		parent::setUp();
		// Instantiate the events calendar and execute the init() function
		$tribe_ecp = TribeEvents::instance();
		$tribe_ecp->init();		
	}
	
	// Tests to make sure that the administrator role has all the default events calendar capabilities attached to them.
	public function test_administrator_role_capabilities() {
	
		$role = get_role('administrator');
		
		// Make sure the role name is correct.
		$this->assertInstanceOf( 'WP_Role', $role );
		
		// Check to make sure the user has all the tribe capabilities.
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
	
	public function test_editor_role_capabilities() {
	
		$role = get_role('editor');
		
		// Make sure the role name is correct.
		$this->assertInstanceOf( 'WP_Role', $role );
		
		// Check to make sure the user has all the tribe capabilities.
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
	
	public function test_author_role_capabilities() {
	
		$role = get_role('author');
		
		// Make sure the role name is correct.
		$this->assertInstanceOf( 'WP_Role', $role );
		
		// Check to make sure the user has all the tribe capabilities.
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
	
	public function test_contributor_role_capabilities() {
	
		$role = get_role('contributor');
		
		// Make sure the role name is correct.
		$this->assertInstanceOf( 'WP_Role', $role );
		
		// Check to make sure the user has all the tribe capabilities.
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
	
		public function test_subscriber_role_capabilities() {
	
		$role = get_role('subscriber');
		
		// Make sure the role name is correct.
		$this->assertInstanceOf( 'WP_Role', $role );
		
		// Check to make sure the user has all the tribe capabilities.
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
		$this->assertFalse( $role->has_cap( 'read_private_tribe_events' ) );}
	}