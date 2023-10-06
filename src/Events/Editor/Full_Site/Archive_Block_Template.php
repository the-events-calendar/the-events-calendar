<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;

/**
 * Class Archive_Block_Template
 *
 * @since   TBD
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Archive_Block_Template extends \Tribe__Editor__Blocks__Abstract implements Block_Template_Contract {
	/**
	 * @since TBD
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * @since TBD
	 *
	 * @return string The WP Block Template ID.
	 */
	public function id(): string {
		return $this->get_namespace() . '//' . $this->slug();
	}

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since TBD
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug(): string {
		return 'archive-events';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since TBD
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes() {
		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since TBD
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
	 * Creates if non-existent theme post, then returns the WP_Block_Template object for archive events.
	 *
	 * @since TBD
	 *
	 * @return null|WP_Block_Template The hydrated archive events template object.
	 */
	public function get_block_template(): ?WP_Block_Template {
		$wp_block_template = Template_Utils::find_block_template_by_post( $this->slug(), $this->get_namespace() );

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( ! $wp_block_template ) {
			$insert = [
				'post_name'    => $this->slug(),
				'post_title'   => esc_html_x( 'Calendar Views (Event Archive)', 'The Full Site editor block navigation title', 'the-events-calendar' ),
				'post_excerpt' => esc_html_x( 'Displays the calendar views.', 'The Full Site editor block navigation description', 'the-events-calendar' ),
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_content' => Template_Utils::inject_theme_attribute_in_content( file_get_contents(
					Tribe__Events__Main::instance()->plugin_path . '/src/Events/Editor/Full_Site/Templates/archive-events.html'
				) ),
				'tax_input'    => [
					'wp_theme' => $this->get_namespace()
				]
			];

			// Create this template.
			$wp_block_template = Template_Utils::save_block_template( $insert );
		}

		// Validate we did stuff correctly.
		if ( ! $wp_block_template instanceof WP_Block_Template ) {
			do_action( 'tribe_log', 'error',
				'Failed locating our WP_Block_Template for the Archive Events Block', [
					'method'    => __METHOD__,
					'slug'      => $this->slug(),
					'namespace' => $this->get_namespace()
				] );
		}

		return $wp_block_template;
	}
}
