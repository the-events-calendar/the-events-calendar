<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe__Events__Main;

use TEC\Common\Editor\Full_Site\Template_Utils;

use WP_Block_Template;

/**
 * Class Templates
 *
 * @since   TBD
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Templates {

	public static $archive_slug = 'archive-' . Tribe__Events__Main::POSTTYPE;

	/**
	 * Modify the available Templates so that people can edit the template.
	 *
	 * @since TBD
	 *
	 * @param array     $query_result
	 * @param \WP_Query $query
	 * @param string    $template_type Which kind of template we are talking about.
	 *
	 * @return array
	 */
	public function add_events_archive( array $query_result, \WP_Query $query, string $template_type ) {
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


	public function get_template_events_archive() {
		$template                 = new WP_Block_Template();
		$template->id             = 'the-events-calendar//archive-events';
		$template->theme          = 'The Events Calendar';
		$template->content        = Template_Utils::inject_theme_attribute_in_content( '
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:tribe/archive-events {} /-->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
		' );
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
}