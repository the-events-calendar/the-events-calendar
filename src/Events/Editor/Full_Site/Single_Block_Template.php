<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Single_Event;
use Tribe__Events__Main;
use TEC\Common\Editor\Full_Site\Template_Utils;
use WP_Block_Template;
use WP_Post;
use WP_Query;

/**
 * Class Single_Block_Templates
 *
 * @since TBD
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Single_Block_Template {
	/**
	 * Modifies the available templates to include the single events.
	 *
	 * This method can be used to add specific templates for single events in
	 * the Full Site Editor (FSE) or any other templating mechanism.
	 *
	 * @since TBD
	 *
	 * @param WP_Block_Template[] $query_result  Array of found block templates.
	 * @param array               $query         Arguments to retrieve templates.
	 * @param string              $template_type wp_template or wp_template_part.
	 *
	 * @return array Modified list of block templates.
	 */
	public function add_event_single( array $query_result, $query, string $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		if (
			! empty( $query['slug__in'] )
			&& ! in_array( tribe( Single_Event::class )->slug(), $query['slug__in'], true )
		) {
			return $query_result;
		}

		$query_result[] = $this->get_template_event_single();

		return $query_result;
	}

	/**
	 * Creates and returns the template object for single events.
	 *
	 * This method can be used to define or retrieve a template object for
	 * displaying single events.
	 *
	 * @since TBD
	 *
	 * @return WP_Block_Template The single events template object.
	 */
	public function get_template_event_single() {
		$template      = new WP_Block_Template();
		$single_event_block = tribe( Single_Event::class );

		// Let's see if we have a saved template?
		$wp_query_args  = [
			'post_name__in'  => [ $single_event_block->slug() ],
			'post_type'      => 'wp_template',
			'post_status'    => [ 'auto-draft', 'draft', 'publish', 'trash' ],
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'tax_query'      => [
				[
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => $single_event_block->get_namespace(),
				],
			],
		];
		$template_query = new WP_Query( $wp_query_args );
		$posts          = $template_query->posts;

		$wp_block_template = Template_Utils::find_block_template_by_post($single_event_block->slug(), $single_event_block->get_namespace());

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( !$wp_block_template) {
			$insert = [
				'post_name'    => $single_event_block->slug(),
				'post_title'   => esc_html_x( 'Event Single', 'The Full Site editor block navigation title', 'the-events-calendar' ),
				'post_excerpt' => esc_html_x( 'Displays a single event.', 'The Full Site editor block navigation description', 'the-events-calendar' ),
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_content' => Template_Utils::inject_theme_attribute_in_content( file_get_contents(
					Tribe__Events__Main::instance()->plugin_path . '/src/Events/Editor/Full_Site/Templates/single-event.html'
				) )
			];
			// Create this template.
			$id = wp_insert_post( $insert );

			// Setup our "theme" term, for the taxonomy query.
			$term = get_term_by( 'name', $single_event_block->get_namespace(), 'wp_theme', ARRAY_A );
			if ( ! $term ) {
				wp_insert_term( $single_event_block->get_namespace(), 'wp_theme' );
			}
			wp_set_post_terms( $id, $single_event_block->get_namespace(), 'wp_theme' );
			$post = get_post( $id );
		} else {
			// We were already initialized, load our saved template.
			$post = $posts[0];
		}

		// Validate we did stuff correctly.
		if ( ! $post instanceof WP_Post ) {
			do_action( 'tribe_log', 'error',
				'Failed locating our Post for the Single Event Block Template', [
					'method' => __METHOD__,
				] );

			// Might as well bail, avoid errors below.
			return $template;
		}

		// Hydrate our template with the saved data.
		$template->wp_id          = $post->ID;
		$template->id             = $single_event_block->get_namespace() . '//' . $single_event_block->slug();
		$template->theme          = $single_event_block->get_namespace();
		$template->content        = $post->post_content;
		$template->slug           = $single_event_block->slug();
		$template->source         = 'custom';
		$template->type           = 'wp_template';
		$template->title          = $post->post_title;
		$template->description    = $post->post_excerpt;
		$template->status         = $post->post_status;
		$template->has_theme_file = false;
		$template->is_custom      = true;
		$template->author         = $post->post_author;

		return $template;
	}
}
