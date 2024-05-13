<?php

namespace TEC\Events\Block_Templates\Single_Event;

use TEC\Events\Blocks\Single_Event\Block;
use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;
use TEC\Events\Block_Templates\Block_Template_Contract;

/**
 * Class Single_Block_Template
 *
 * @since 6.3.3 Moved and decoupled from Block API requirements, focusing on Template requirements.
 * @since   6.2.7
 *
 * @package TEC\Events\Block_Templates\Single_Event
 */
class Single_Block_Template implements Block_Template_Contract {
	/**
	 * @since 6.3.3
	 *
	 * @var Block The registered block for this template.
	 */
	protected Block $block;

	/**
	 * Constructor for Single Event Block Template.
	 *
	 * @since 6.3.3
	 *
	 * @param Block $block The registered Block for Single Event.
	 */
	public function __construct( Block $block ) {
		$this->block = $block;
	}

	/**
	 * Which is the name/slug of this template block.
	 *
	 * @since 6.3.3
	 *
	 * @return string
	 */
	public function slug(): string {
		return $this->block->slug();
	}

	/**
	 * The ID of this block.
	 *
	 * @since 6.2.7
	 *
	 * @return string The WP Block Template ID.
	 */
	public function id(): string {
		return $this->block->get_namespace() . '//' . $this->block->slug();
	}

	/**
	 * Creates then returns the WP_Block_Template object for single event.
	 *
	 * @since 6.2.7
	 *
	 * @return null|WP_Block_Template The hydrated single event template object.
	 */
	protected function create_wp_block_template(): ?WP_Block_Template {
		$post_title = sprintf(
			/* translators: %s: Event (singular) */
			esc_html_x( 'Single %s', 'The Full Site editor event block navigation title', 'the-events-calendar' ),
			tribe_get_event_label_singular()
		);
		$post_excerpt = sprintf(
			/* translators: %s: event (singular) */
			esc_html_x( 'Displays a single %s.', 'The Full Site editor event block navigation description', 'the-events-calendar' ),
			tribe_get_event_label_singular_lowercase()
		);
		$insert       = [
			'post_name'    => $this->block->slug(),
			'post_title'   => $post_title,
			'post_excerpt' => $post_excerpt,
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'post_content' => Template_Utils::inject_theme_attribute_in_content(
				file_get_contents(
					Tribe__Events__Main::instance()->plugin_path . '/src/Events/Block_Templates/Single_Event/templates/single-event.html'
				)
			),
			'tax_input'    => [
				'wp_theme' => $this->block->get_namespace(),
			],
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
		$wp_block_template = Template_Utils::find_block_template_by_post( $this->block->slug(), $this->block->get_namespace() );

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( ! $wp_block_template ) {
			$wp_block_template = $this->create_wp_block_template();
		}

		// Validate we did stuff correctly.
		if ( ! $wp_block_template instanceof WP_Block_Template ) {
			do_action(
				'tribe_log',
				'error',
				'Failed locating our WP_Block_Template for the Single Event Block',
				[
					'method'    => __METHOD__,
					'slug'      => $this->block->slug(),
					'namespace' => $this->block->get_namespace(),
				]
			);
		}

		return $wp_block_template;
	}
}
