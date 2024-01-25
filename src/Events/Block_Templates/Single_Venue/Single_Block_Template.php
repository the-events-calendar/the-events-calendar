<?php

namespace TEC\Events\Block_Templates\Single_Venue;

use TEC\Events\Blocks\Single_Venue\Block;
use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;
use TEC\Events\Block_Templates\Block_Template_Contract;

/**
 * Class Single_Block_Template
 *
 * @since TBD
 *
 * @package TEC\Events\Block_Templates\Single_Venue
 */
class Single_Block_Template implements Block_Template_Contract {

	/**
	 * @since TBD
	 *
	 * @var Block The registered block for this template.
	 */
	protected Block $block;

	/**
	 * Constructor for Single Venue Block Template.
	 *
	 * @since TBD
	 *
	 * @param Block $block The registered Block for Single Venue.
	 */
	public function __construct( Block $block ) {
		$this->block = $block;
	}

	/**
	 * The ID of this block.
	 *
	 * @since TBD
	 *
	 * @return string The WP Block Template ID.
	 */
	public function id(): string {
		return $this->block->get_namespace() . '//' . $this->block->slug();
	}

	/**
	 * Creates then returns the WP_Block_Template object for single venue.
	 *
	 * @since TBD
	 *
	 * @return null|WP_Block_Template The hydrated single event template object.
	 */
	protected function create_wp_block_template(): ?WP_Block_Template {
		/* translators: %s: Event (singular) */
		$post_title = sprintf(
			esc_html_x( 'Single %s', 'The Full Site editor venue block navigation title', 'the-events-calendar' ),
			tribe_get_venue_label_singular()
		);
		/* translators: %s: event (singular) */
		$post_excerpt = sprintf(
			esc_html_x( 'Displays a single %s.', 'The Full Site editor venue block navigation description', 'the-events-calendar' ),
			tribe_get_venue_label_singular_lowercase()
		);
		$insert       = [
			'post_name'    => $this->block->slug(),
			'post_title'   => $post_title,
			'post_excerpt' => $post_excerpt,
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'post_content' => Template_Utils::inject_theme_attribute_in_content( file_get_contents(
				Tribe__Events__Main::instance()->plugin_path . '/src/Events/Block_Templates/Single_Venue/templates/single-venue.html'
			) ),
			'tax_input'    => [
				'wp_theme' => $this->block->get_namespace()
			]
		];

		// Create this template.
		return Template_Utils::save_block_template( $insert );
	}

	/**
	 * Creates if non-existent theme post, then returns the WP_Block_Template object for single events.
	 *
	 * @since TBD
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
			do_action( 'tribe_log', 'error',
				'Failed locating our WP_Block_Template for the Single Venue Block', [
					'method'    => __METHOD__,
					'slug'      => $this->block->slug(),
					'namespace' => $this->block->get_namespace()
				] );
		}

		return $wp_block_template;
	}
}
