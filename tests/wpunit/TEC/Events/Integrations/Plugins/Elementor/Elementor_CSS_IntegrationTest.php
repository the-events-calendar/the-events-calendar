<?php
/**
 * WPUnit tests for Elementor CSS integration (single event template styles and CSS generation).
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */

namespace TEC\Events\Integrations\Plugins\Elementor;

use TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Static;
use TEC\Events\Integrations\Plugins\Elementor\Template\Importer;
use Tribe\Events\Test\Factories\Event;
use Tribe\Tests\Traits\With_Uopz;

class Elementor_CSS_IntegrationTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		// Stub Elementor's Source_Local so Importer::get_template() can run when Elementor is not loaded.
		require_once __DIR__ . '/Elementor_Source_Local_Stub.php';
		// Allow Elementor integration to load so Assets_Manager and Importer are available.
		if ( ! defined( 'ELEMENTOR_PATH' ) ) {
			define( 'ELEMENTOR_PATH', 'elementor' );
		}
		// Resolve Controller so it registers the elementor/loaded action, then fire it.
		tribe( Controller::class );
		if ( ! did_action( 'elementor/loaded' ) ) {
			do_action( 'elementor/loaded' );
		}
	}

	public function setUp(): void {
		parent::setUp();
		$this->factory()->event = new Event();
	}

	/**
	 * It should not enqueue single event template style when not on single event page.
	 *
	 * @test
	 */
	public function should_not_enqueue_single_event_template_style_when_not_on_single_event(): void {
		// Not on a single event (we have not called go_to for an event).
		$assets_manager = tribe( Assets_Manager::class );
		$assets_manager->enqueue_single_event_template_styles();

		$wp_styles = wp_styles();
		$registered = $wp_styles->registered;
		$enqueued = $wp_styles->queue;
		$has_elementor_template_style = false;
		foreach ( array_merge( array_keys( $registered ), $enqueued ) as $handle ) {
			if ( strpos( (string) $handle, 'elementor-event-template-' ) === 0 ) {
				$has_elementor_template_style = true;
				break;
			}
		}
		$this->assertFalse( $has_elementor_template_style, 'Should not enqueue Elementor template style when not on single event.' );
	}

	/**
	 * It should not enqueue single event template style when template is not set.
	 *
	 * @test
	 */
	public function should_not_enqueue_single_event_template_style_when_template_not_set(): void {
		$event_id = $this->factory()->event->create(
			[
				'post_title' => 'Event for template test',
				'when'       => '+1 week',
			]
		);
		$this->go_to( get_permalink( $event_id ) );

		// Ensure no template is stored (option empty or no matching post).
		$option_key = 'tec_events_elementor_template_imported';
		$templates  = get_option( $option_key, [] );
		if ( is_array( $templates ) && isset( $templates[ Event_Single_Static::class ] ) ) {
			unset( $templates[ Event_Single_Static::class ] );
			update_option( $option_key, $templates );
		}

		$assets_manager = tribe( Assets_Manager::class );
		$assets_manager->enqueue_single_event_template_styles();

		$wp_styles = wp_styles();
		$has_elementor_template_style = false;
		foreach ( array_merge( array_keys( $wp_styles->registered ), $wp_styles->queue ) as $handle ) {
			if ( strpos( (string) $handle, 'elementor-event-template-' ) === 0 ) {
				$has_elementor_template_style = true;
				break;
			}
		}
		$this->assertFalse( $has_elementor_template_style, 'Should not enqueue when no template is set.' );
	}

	/**
	 * It should not enqueue single event template style when CSS file does not exist.
	 *
	 * @test
	 */
	public function should_not_enqueue_single_event_template_style_when_css_file_does_not_exist(): void {
		$event_id = $this->factory()->event->create(
			[
				'post_title' => 'Event for CSS missing test',
				'when'       => '+1 week',
			]
		);
		$template_post_id = $this->create_elementor_library_template_post();
		$this->set_template_option( $template_post_id );
		$this->go_to( get_permalink( $event_id ) );
		// Do not create the CSS file so file_exists() is false and we avoid enqueuing (no 404).

		$assets_manager = tribe( Assets_Manager::class );
		$assets_manager->enqueue_single_event_template_styles();

		$wp_styles = wp_styles();
		$handle = 'elementor-event-template-' . $template_post_id;
		$this->assertFalse(
			isset( $wp_styles->registered[ $handle ] ) || in_array( $handle, $wp_styles->queue, true ),
			'Should not enqueue when CSS file does not exist (avoids 404).'
		);
	}

	/**
	 * It should enqueue single event template style when CSS file exists.
	 *
	 * @test
	 */
	public function should_enqueue_single_event_template_style_when_css_file_exists(): void {
		$event_id = $this->factory()->event->create(
			[
				'post_title' => 'Event for CSS exists test',
				'when'       => '+1 week',
			]
		);
		$template_post_id = $this->create_elementor_library_template_post();
		$this->set_template_option( $template_post_id );
		$this->create_elementor_css_file( $template_post_id );
		$this->go_to( get_permalink( $event_id ) );

		$assets_manager = tribe( Assets_Manager::class );
		$assets_manager->enqueue_single_event_template_styles();

		$wp_styles = wp_styles();
		$handle = 'elementor-event-template-' . $template_post_id;
		$this->assertArrayHasKey(
			$handle,
			$wp_styles->registered,
			'Should register and enqueue the template CSS when the file exists.'
		);
		$this->assertContains( $handle, $wp_styles->queue, 'Template style should be in the queue when file exists.' );
	}

	/**
	 * It should not throw when generate_css_for_document is called and Elementor CSS class is not loaded.
	 *
	 * @test
	 */
	public function should_not_throw_when_generate_css_for_document_called_without_elementor_css_class(): void {
		$importer = tribe( Importer::class );
		$reflection = new \ReflectionClass( $importer );
		$method = $reflection->getMethod( 'generate_css_for_document' );
		$method->setAccessible( true );

		// When \Elementor\Core\Files\CSS\Post does not exist, the method returns early without throwing.
		$thrown = null;
		try {
			$method->invoke( $importer, 12345 );
		} catch ( \Throwable $e ) {
			$thrown = $e;
		}

		$this->assertNull( $thrown, 'generate_css_for_document should not throw when Elementor CSS class is not loaded.' );
	}

	/**
	 * Creates a post of type elementor_library (Elementor template) for tests.
	 *
	 * @return int Post ID.
	 */
	protected function create_elementor_library_template_post(): int {
		return (int) $this->factory()->post->create(
			[
				'post_type'   => 'elementor_library',
				'post_status' => 'publish',
				'post_title'  => 'TEC Single Event Template (test)',
			]
		);
	}

	/**
	 * Sets the tec_events_elementor_template_imported option so get_template() returns the given post.
	 *
	 * @param int $template_post_id Post ID of the elementor_library template.
	 */
	protected function set_template_option( int $template_post_id ): void {
		$option_key = 'tec_events_elementor_template_imported';
		$templates  = get_option( $option_key, [] );
		if ( ! is_array( $templates ) ) {
			$templates = [];
		}
		$templates[ Event_Single_Static::class ] = $template_post_id;
		update_option( $option_key, $templates );
	}

	/**
	 * Creates the physical CSS file in wp-content/uploads/elementor/css/post-{id}.css.
	 *
	 * @param int $template_post_id Template post ID.
	 */
	protected function create_elementor_css_file( int $template_post_id ): void {
		$upload_dir = wp_upload_dir();
		$dir        = $upload_dir['basedir'] . '/elementor/css';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$css_path = $dir . '/post-' . $template_post_id . '.css';
		file_put_contents( $css_path, '/* TEC Elementor template CSS */' );
	}
}
