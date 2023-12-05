<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;

/**
 * Class Single_Block_Templates
 *
 * @since   6.2.7
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Single_Block_Template extends \Tribe__Editor__Blocks__Abstract implements Block_Template_Contract {
	/**
	 * @since 6.2.7
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since 6.2.7
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug(): string {
		return 'single-event';
	}

	/**
	 * The ID of this block.
	 *
	 * @since 6.2.7
	 *
	 * @return string The WP Block Template ID.
	 */
	public function id(): string {
		return $this->get_namespace() . '//' . $this->slug();
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since 6.2.7
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes(): array {
		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 6.2.7
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The block HTML.
	 */
	public function render( $attributes = [] ): string {
		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context.
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}

	/**
	 * Creates then returns the WP_Block_Template object for single event.
	 *
	 * @since 6.2.7
	 *
	 * @return null|WP_Block_Template The hydrated single event template object.
	 */
	protected function create_wp_block_template(): ?WP_Block_Template {
		/* translators: %s: Event (singular) */
		$post_title = sprintf(
			esc_html_x( '%s Single', 'The Full Site editor block navigation title', 'the-events-calendar' ),
			tribe_get_event_label_singular()
		);
		/* translators: %s: event (singular) */
		$post_excerpt = sprintf(
			esc_html_x( 'Displays a single %s.', 'The Full Site editor block navigation description', 'the-events-calendar' ),
			tribe_get_event_label_singular_lowercase()
		);
		$insert       = [
			'post_name'    => $this->slug(),
			'post_title'   => $post_title,
			'post_excerpt' => $post_excerpt,
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'post_content' => Template_Utils::inject_theme_attribute_in_content( file_get_contents(
				Tribe__Events__Main::instance()->plugin_path . '/src/Events/Blocks/Single_Event_Template/templates/single-event.html'
			) ),
			'tax_input'    => [
				'wp_theme' => $this->get_namespace()
			]
		];

		// Create this template.
		return Template_Utils::save_block_template( $insert );
	}

	/**
	 * Creates if non-existent theme post, then returns the WP_Block_Template object for single events.
	 *
	 * @since 6.2.7
	 *
	 * @return null|WP_Block_Template The hydrated single events template object.
	 */
	public function get_block_template(): ?WP_Block_Template {
		$wp_block_template = Template_Utils::find_block_template_by_post( $this->slug(), $this->get_namespace() );

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( ! $wp_block_template ) {
			$wp_block_template = $this->create_wp_block_template();
		}

		// Validate we did stuff correctly.
		if ( ! $wp_block_template instanceof WP_Block_Template ) {
			do_action( 'tribe_log', 'error',
				'Failed locating our WP_Block_Template for the Single Event Block', [
					'method'    => __METHOD__,
					'slug'      => $this->slug(),
					'namespace' => $this->get_namespace()
				] );
		}

		return $wp_block_template;
	}
}
