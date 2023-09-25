<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Archive_Events;
use Tribe__Events__Main;

use TEC\Common\Editor\Full_Site\Template_Utils;

use WP_Block_Template;
use WP_Post;
use WP_Query;

/**
 * Class Templates
 *
 * @since   5.14.2
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Templates {

	/**
	 * The archive slug.
	 *
	 * @since 5.14.2
	 *
	 * @var string The archive slug.
	 */
	public static $archive_slug = 'archive-' . Tribe__Events__Main::POSTTYPE;

	/**
	 * The single slug.
	 *
	 * @since TBD
	 *
	 * @var string The single slug.
	 */
	public static $single_slug = 'single-' . Tribe__Events__Main::POSTTYPE;

	/**
	 * Modify the available Templates so that people can edit the template.
	 *
	 * @since 5.14.2
	 *
	 * @param WP_Block_Template[] $query_result  Array of found block templates.
	 * @param array               $query         {
	 *                                           Optional. Arguments to retrieve templates.
	 *
	 * @type array                $slug__in      List of slugs to include.
	 * @type int                  $wp_id         Post ID of customized template.
	 * }
	 *
	 * @param string              $template_type wp_template or wp_template_part.
	 *
	 * @return array
	 */
	public function add_events_archive( array $query_result, $query, string $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		// If we are not querying for all or the specific one we want we bail.
		if (
			! empty( $query['slug__in'] )
			&& ! in_array( tribe( Archive_Events::class )->slug(), $query['slug__in'], true )
		) {
			return $query_result;
		}

		$query_result[] = $this->get_template_events_archive();

		return $query_result;
	}

	/**
	 * Returns the constructed template object for the query.
	 *
	 * @since 5.14.2
	 * @since TBD Updated with WP_Block_Template initialization from the database for Site Editor.
	 *
	 * @return WP_Block_Template A reference to the template object for the query.
	 */
	public function get_template_events_archive() {
		$template      = new WP_Block_Template();
		$archive_block = tribe( Archive_Events::class );

		// Let's see if we have a saved template?
		$wp_query_args  = [
			'post_name__in'  => [ $archive_block->slug() ],
			'post_type'      => 'wp_template',
			'post_status'    => [ 'auto-draft', 'draft', 'publish', 'trash' ],
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'tax_query'      => [
				[
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => $archive_block->get_namespace(),
				],
			],
		];
		$template_query = new WP_Query( $wp_query_args );
		$posts          = $template_query->posts;

		// If empty, this is our first time loading our Block Template. Let's create it.
		if ( empty( $posts ) ) {
			$insert = [
				'post_name'    => $archive_block->slug(),
				'post_title'   => esc_html_x( 'Calendar Views (Event Archive)', 'The Full Site editor block navigation title', 'the-events-calendar' ),
				'post_excerpt' => esc_html_x( 'Displays the calendar views.', 'The Full Site editor block navigation description', 'the-events-calendar' ),
				'post_type'    => 'wp_template',
				'post_status'  => 'publish',
				'post_content' => Template_Utils::inject_theme_attribute_in_content( file_get_contents(
					Tribe__Events__Main::instance()->plugin_path . '/src/Events/Editor/Full_Site/Templates/archive-events.html'
				) )
			];
			// Create this template.
			$id = wp_insert_post( $insert );

			// Setup our "theme" term, for the taxonomy query.
			$term = get_term_by( 'name', $archive_block->get_namespace(), 'wp_theme', ARRAY_A );
			if ( ! $term ) {
				wp_insert_term( $archive_block->get_namespace(), 'wp_theme' );
			}
			wp_set_post_terms( $id, $archive_block->get_namespace(), 'wp_theme' );
			$post = get_post( $id );
		} else {
			// We were already initialized, load our saved template.
			$post = $posts[0];
		}

		// Validate we did stuff correctly.
		if ( ! $post instanceof WP_Post ) {
			do_action( 'tribe_log', 'error',
				'Failed locating our Post for the Archive Events Block Template', [
					'method' => __METHOD__,
				] );

			// Might as well bail, avoid errors below.
			return $template;
		}

		// Hydrate our template with the saved data.
		$template->wp_id          = $post->ID;
		$template->id             = $archive_block->get_namespace() . '//' . $archive_block->slug();//$archive_block->name();
		$template->theme          = $archive_block->get_namespace();
		$template->content        = $post->post_content;
		$template->slug           = $archive_block->slug();//@todo static::$archive_slug;
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
			&& ! in_array( static::$single_slug, $query['slug__in'], true )
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
		$template_content = file_get_contents(
			Tribe__Events__Main::instance()->plugin_path . '/src/Events/Editor/Full_Site/Templates/single-event.html'
		);

		$template                 = new WP_Block_Template();
		$template->id             = 'the-events-calendar//single-event';
		$template->theme          = 'The Events Calendar';
		$template->content        = Template_Utils::inject_theme_attribute_in_content( $template_content );
		$template->slug           = static::$single_slug;
		$template->source         = 'custom';
		$template->type           = 'wp_template';
		$template->title          = esc_html_x( 'Event Single', 'The Full Site editor block navigation title', 'the-events-calendar' );
		$template->description    = esc_html_x( 'Displays a single event.', 'The Full Site editor block navigation description', 'the-events-calendar' );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = true;

		return $template;
	}
}
