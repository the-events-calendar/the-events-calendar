<?php
namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;

class generalTest extends WPTestCase {

	protected $content_w_blocks = <<< HTML
<!-- wp:tribe/event-datetime /-->

<!-- wp:paragraph -->
<p>Before embed. </p>
<!-- /wp:paragraph -->

<!-- wp:core-embed/vimeo {"url":"https://vimeo.com/346787418","type":"video","providerNameSlug":"vimeo","className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed-vimeo wp-block-embed is-type-video is-provider-vimeo wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://vimeo.com/346787418
</div></figure>
<!-- /wp:core-embed/vimeo -->

<!-- wp:shortcode -->
[ embed width="123" height="456"]http://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>After embed. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>After embed 2. </p>
<!-- /wp:paragraph -->

<!-- wp:tribe/event-price /-->

<!-- wp:tribe/event-organizer /-->

<!-- wp:tribe/event-venue /-->

<!-- wp:tribe/event-website /-->

<!-- wp:tribe/event-links /-->

<!-- wp:tribe/related-events /-->
HTML;

	public static function setUpBeforeClass(  ) {
		parent::setUpBeforeClass();
		static::factory()->event = new Event();
	}
	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	public function separated_field_inputs() {
		return [
			[ '', ' | ', 'Hello', 'Hello' ],
			[ 'Something', ' | ', 'Hello', 'Something | Hello' ],
			[ 'Something', ' | ', '', 'Something' ],
			[ '', '', '', '' ],
			[ 'Something', '', '', 'Something' ],
			[ 'Something', '', 'Hello', 'SomethingHello' ],
		];
	}

	/**
	 * @dataProvider separated_field_inputs
	 */
	public function test_tribe_separated_field( $body, $sep, $field, $expected ) {
		$this->assertEquals( $expected, tribe_separated_field( $body, $sep, $field ) );
	}

	/**
	 * It should remove blocks from excerpt correctly
	 *
	 * @test
	 */
	public function should_remove_blocks_from_excerpt_correctly() {
		add_filter( 'tribe_events_excerpt_blocks_removal', '__return_true' );
		// Ensure the excerpt is empty to avoid auto-filling by the factory.
		$event = static::factory()->event->create( [ 'post_content' => $this->content_w_blocks, 'post_excerpt' => '' ] );

		$excerpt_wo_blocks = "<p>Before embed. After embed. After embed 2.</p>\n";

		$this->assertEquals( $excerpt_wo_blocks, tribe_events_get_the_excerpt( $event ) );

	}

	/**
	 * It should correctly render excerpt w/ blocks
	 *
	 * @test
	 */
	public function should_correctly_render_excerpt_w_blocks() {
		add_filter( 'tribe_events_excerpt_blocks_removal', '__return_false' );
		// Ensure the excerpt is empty to avoid auto-filling by the factory.
		$event = static::factory()->event->create( [ 'post_content' => $this->content_w_blocks, 'post_excerpt' => '' ] );

		$excerpt_w_blocks = "<p>Before embed. https://vimeo.com/346787418 http://www.youtube.com/watch?v=dQw4w9WgXcQ After embed. After embed 2.</p>\n";

		$this->assertEquals( $excerpt_w_blocks, tribe_events_get_the_excerpt( $event ) );
	}
}
