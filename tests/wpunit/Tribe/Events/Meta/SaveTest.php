<?php
namespace Tribe\Events\Meta;

use Tribe__Events__Main as Main;
use Tribe__Events__Meta__Context as Context;
use Tribe__Events__Meta__Save as Save;
use Tribe__Editor as Editor;
use Tribe\Editor\Compatibility\Classic_Editor as Classic_Editor;

class SaveTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * @var Context
	 */
	protected $context;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->post    = $this->factory()->post->create_and_get( [ 'post_type' => Main::POSTTYPE ] );
		$this->context = $this->prophesize( Context::class );
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Save::class, $sut );
	}

	/**
	 * @test
	 * it should not save if it is not event
	 */
	public function it_should_not_save_if_it_is_not_event() {
		$this->post = $this->factory()->post->create_and_get( [ 'post_type' => 'post' ] );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save if it is doing ajax
	 */
	public function it_should_not_save_if_it_is_doing_ajax() {
		$this->context->doing_ajax()->willReturn( true );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save on autosave
	 */
	public function it_should_not_save_on_autosave() {
		$this->context->doing_ajax()->willReturn( false );
		$parent     = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_name' => 'some' ] );
		$this->post = $this->factory()->post->create_and_get( [ 'post_type' => 'revision', 'post_parent' => $parent, 'post_name' => 'some-autosave' ] );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save on auto draft
	 */
	public function it_should_not_save_on_auto_draft() {
		$this->context->doing_ajax()->willReturn( false );
		$this->post = $this->factory()->post->create_and_get( [ 'post_type' => Main::POSTTYPE, 'post_status' => 'auto-draft' ] );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save when bulk editing
	 */
	public function it_should_not_save_when_bulk_editing() {
		$this->context->doing_ajax()->willReturn( false );
		$this->context->is_bulk_editing()->willReturn( true );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save when inline saving
	 */
	public function it_should_not_save_when_inline_saving() {
		$this->context->doing_ajax()->willReturn( false );
		$this->context->is_bulk_editing()->willReturn( false );
		$this->context->is_inline_save()->willReturn( true );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save on auxiliary save
	 */
	public function it_should_not_save_on_auxiliary_save() {
		$this->context->doing_ajax()->willReturn( false );
		$this->context->is_bulk_editing()->willReturn( false );
		$this->context->is_inline_save()->willReturn( false );

		$_POST['post_ID'] = $this->factory()->post->create();

		$sut = $this->make_instance();

		$this->assertFalse( $sut->maybe_save() );
	}

	/**
	 * @test
	 * it should not save if nonce is not present
	 */
	public function it_should_not_save_if_nonce_is_not_present() {
		$this->context->has_nonce()->willReturn( false );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->save() );
	}

	/**
	 * @test
	 * it should not save if nonce is not verified
	 */
	public function it_should_not_save_if_nonce_is_not_verified() {
		$this->context->has_nonce()->willReturn( true );
		$this->context->verify_nonce()->willReturn( false );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->save() );
	}

	/**
	 * @test
	 * it should not save if current user can not edit events
	 */
	public function it_should_not_save_if_current_user_can_not_edit_events() {
		$this->context->has_nonce()->willReturn( true );
		$this->context->verify_nonce()->willReturn( true );
		$this->context->current_user_can_edit_events()->willReturn( false );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->save() );
	}


	/**
	 * @test
	 * it should save when context and posts are ok
	 */
	public function it_should_save_when_context_and_posts_are_ok() {
		$this->context->doing_ajax()->willReturn( false );
		$this->context->is_bulk_editing()->willReturn( false );
		$this->context->is_inline_save()->willReturn( false );
		$this->context->has_nonce()->willReturn( true );
		$this->context->verify_nonce()->willReturn( true );
		$this->context->current_user_can_edit_events()->willReturn( true );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->maybe_save() );
	}

	/**
	 * @return Save
	 */
	private function make_instance() {
		return new Save( $this->post->ID, $this->post, $this->context->reveal() );
	}

	/**
	 * @return Classic_Editor
	 */
	protected function make_classic_instance() {
		return new Classic_Editor();
	}

	/**
	 * It should save events when classic editor and gutenberg blocks are activated
	 *
	 * @test
	 */
	public function should_save_events_when_classic_editor_and_gutenberg_blocks_are_activated() {
		global $post_data;

		/** @var \Tribe__Events__Editor__Compatibility $compatibility */
		$compatibility = tribe( 'events.editor.compatibility' );
		// Enable checkbox value
		\Tribe__Settings_Manager::set_option( $compatibility::$blocks_editor_key, true );
		// Fake classic editor plugin is active.
		add_filter( 'tribe_editor_should_load_blocks', '__return_false' );
		// Make sure user is logged in so we have an admin with permissions to create events.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$id = wp_insert_post( [
			'post_title' => 'A test event',
			'post_type' => \Tribe__Events__Main::POSTTYPE,
			'post_status' => 'publish',
		] );

		$values = [
			'EventStartDate'     => '2020-01-01',
			'EventEndDate'       => '2020-01-03',
			'EventStartHour'     => '01',
			'EventStartMinute'   => '15',
			'EventStartMeridian' => 'am',
			'EventEndHour'       => '03',
			'EventEndMinute'     => '25',
			'EventEndMeridian'   => 'pm',
			'EventTimezone'      => 'Europe/Paris',
			'ecp_nonce'          => wp_create_nonce( \Tribe__Events__Main::POSTTYPE ),
		];
		// Fake values on $_POST when the hook is fired.
		foreach ( $values as $key => $value ) {
			$_POST[ $key ]     = $value;
			$_GET[ $key ]      = $value;
			$post_data[ $key ] = $value;
		}

		$event = get_post( $id );

		// Fire action on the post.
		do_action( 'save_post', $id, $event, false );

		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( 'Europe/Paris', get_post_meta( $event->ID, '_EventTimezone', true ) );
		$this->assertEquals( '2020-01-01 01:15:00', get_post_meta( $event->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2020-01-03 15:25:00', get_post_meta( $event->ID, '_EventEndDate', true ) );
		$this->assertEquals( 'A test event', $event->post_title );
		$this->assertEmpty( $event->post_content );

		// Cleanup
		\Tribe__Settings_Manager::set_option( $compatibility::$blocks_editor_key, null );
		foreach ( $values as $key => $value ) {
			unset( $_POST[ $key ] );
		}

		remove_filter( 'tribe_editor_should_load_blocks', '__return_false' );
	}
}
