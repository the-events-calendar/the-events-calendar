<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;

_deprecated_file( __FILE__, '6.3.3' );

/**
 * Class Archive_Block_Template
 *
 * @since      6.2.7
 * @deprecated 6.3.3
 *
 * @package    TEC\Events\Editor\Full_Site
 */
class Archive_Block_Template extends \Tribe__Editor__Blocks__Abstract implements Block_Template_Contract {
	/**
	 * @since 6.2.7
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * @since       6.2.7
	 * @return string The WP Block Template ID.
	 * @deprecated  6.3.3
	 */
	public function id(): string {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		return $this->get_namespace() . '//' . $this->slug();
	}

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since       6.2.7
	 * @return string The name/slug of this block.
	 * @deprecated  6.3.3
	 */
	public function slug(): string {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		return 'archive-events';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since       6.2.7
	 * @return array<string,mixed> The array of default attributes.
	 * @deprecated  6.3.3
	 */
	public function default_attributes() {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since       6.2.7
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The block HTML.
	 * @deprecated  6.3.3
	 */
	public function render( $attributes = [] ): string {
		_deprecated_function( __FUNCTION__, '6.3.3' );
		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context.
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );
	}

	/**
	 * Creates then returns the WP_Block_Template object for archive events.
	 *
	 * @since       6.2.7
	 * @return null|WP_Block_Template The hydrated archive events template object.
	 * @deprecated  6.3.3
	 */
	protected function create_wp_block_template(): ?WP_Block_Template {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		$post_title   = sprintf(
		/* translators: %s: Event (singular) */ esc_html_x(
			                                        'Calendar Views (%s Archive)', 'The Full Site editor block navigation title', 'the-events-calendar'
		                                        ), tribe_get_event_label_singular()
		);
		$post_excerpt = esc_html_x( 'Displays the calendar views.', 'The Full Site editor block navigation description', 'the-events-calendar' );
		$insert       = [
			'post_name'    => $this->slug(),
			'post_title'   => $post_title,
			'post_excerpt' => $post_excerpt,
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'post_content' => Template_Utils::inject_theme_attribute_in_content(
				file_get_contents(
					Tribe__Events__Main::instance()->plugin_path . '/src/Events/Blocks/Archive_Events_Template/templates/archive-events.html'
				)
			),
			'tax_input'    => [
				'wp_theme' => $this->get_namespace(),
			],
		];

		// Create this template.
		return Template_Utils::save_block_template( $insert );
	}

	/**
	 * Creates if non-existent theme post, then returns the WP_Block_Template object for archive events.
	 *
	 * @since       6.2.7
	 * @return null|WP_Block_Template The hydrated archive events template object.
	 * @deprecated  6.3.3
	 */
	public function get_block_template(): ?WP_Block_Template {
		_deprecated_function( __FUNCTION__, '6.3.3' );
		$wp_block_template = Template_Utils::find_block_template_by_post( $this->slug(), $this->get_namespace() );

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( ! $wp_block_template ) {
			$wp_block_template = $this->create_wp_block_template();
		}

		// Validate we did stuff correctly.
		if ( ! $wp_block_template instanceof WP_Block_Template ) {
			do_action( 'tribe_log', 'error', 'Failed locating our WP_Block_Template for the Archive Events Block', [
				'method'    => __METHOD__,
				'slug'      => $this->slug(),
				'namespace' => $this->get_namespace(),
			] );
		}

		return $wp_block_template;
	}
}
