<?php
namespace Tribe\Events\Meta;

use Tribe__Events__Main as Main;
use Tribe__Events__Meta__Context as Context;
use Tribe__Events__Meta__Save as Save;

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

}