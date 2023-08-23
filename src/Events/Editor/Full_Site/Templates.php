<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe__Events__Main;

use TEC\Common\Editor\Full_Site\Template_Utils;

use WP_Block_Template;

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
	 * @param WP_Block_Template[] $query_result Array of found block templates.
	 * @param array               $query        {
	 *                                          Optional. Arguments to retrieve templates.
	 *
	 *     @type array  $slug__in List of slugs to include.
	 *     @type int    $wp_id Post ID of customized template.
	 * }
	 * @param string $template_type wp_template or wp_template_part.
	 *
	 * @return array
	 */
	public function add_events_archive( array $query_result, $query, string $template_type ) {
		if ( is_admin() ) {
			return $query_result;
		}

		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		// If we are not querying for all or the specific one we want we bail.
		if (
			! empty( $query['slug__in'] )
			&& ! in_array( static::$archive_slug, $query['slug__in'], true )
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
	 *
	 * @return WP_Block_Template A reference to the template object for the query.
	 */
	public function get_template_events_archive() {
		$template_content = file_get_contents(
			Tribe__Events__Main::instance()->plugin_path . '/src/Events/Editor/Full_Site/Templates/archive-events.html'
		);

		$template                 = new WP_Block_Template();
		$template->id             = 'the-events-calendar//archive-events';
		$template->theme          = 'The Events Calendar';
		$template->content        = Template_Utils::inject_theme_attribute_in_content( $template_content );
		$template->slug           = static::$archive_slug;
		$template->source         = 'custom';
		$template->theme          = 'The Events Calendar';
		$template->type           = 'wp_template';
		$template->title          = esc_html__( 'Events Archive', 'the-events-calendar' );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = true;

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
		if ( is_admin() ) {
			return $query_result;
		}

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
		$template->title          = esc_html__( 'Event Single', 'the-events-calendar' );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = true;

		return $template;
	}
}
