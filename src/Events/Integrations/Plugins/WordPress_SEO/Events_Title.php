<?php
/**
 * Handles title replacement for Event Categories and Tags to use Yoast SEO titles.
 *
 * When a user sets a custom SEO title via Yoast's term editor, TEC's title-building
 * pipeline normally ignores it. This class intercepts TEC's title generation at two
 * points to ensure the Yoast title takes precedence:
 *
 * 1. `pre_get_document_title` (priority 25, after TEC's priority 20 that returns '')
 *    — if Yoast has a custom term title, return it and short-circuit WordPress's
 *      title-parts pipeline entirely.
 *
 * 2. `tribe_events_views_v2_category_title` — fallback that replaces the category
 *    title part inside TEC's own builder, in case `pre_get_document_title` could
 *    not run (e.g. the query object was not yet set).
 *
 * @since 6.15.17
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Tribe__Events__Main as TEC_Plugin;
use WP_Term;
use WPSEO_Replace_Vars;
use WPSEO_Taxonomy_Meta;

/**
 * Class Events_Title
 *
 * Ensures Yoast SEO custom titles for Event Categories and Tags are used
 * in the document `<title>` tag, matching the `og:title` output.
 *
 * @since 6.15.17
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_Title {

	/**
	 * Register the title hooks.
	 *
	 * @since 6.15.17
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'pre_get_document_title', [ $this, 'pre_get_document_title' ], 25 );

		// Fallback: if pre_get_document_title did not fire (rare), replace the category
		// title part inside TEC's own title builder.
		add_filter( 'tribe_events_views_v2_category_title', [ $this, 'filter_category_title' ], 10, 3 );
	}

	/**
	 * Intercept the document title to use Yoast's title for Event Categories and Tags.
	 *
	 * @since 6.15.17
	 *
	 * @param string $title The current title value ('' after TEC's filter).
	 *
	 * @return string The Yoast title if available, otherwise the original title.
	 */
	public function pre_get_document_title( $title ) {
		if ( is_admin() ) {
			return $title;
		}

		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return $title;
		}

		if ( ! $this->is_supported_taxonomy( $term ) ) {
			return $title;
		}

		$yoast_title = $this->get_yoast_title_for_term( $term );

		return ! empty( $yoast_title ) ? $yoast_title : $title;
	}

	/**
	 * Filter the Event Category title to use Yoast's title if available.
	 *
	 * This is a fallback that fires inside TEC's `build_category_title()` method.
	 * If `pre_get_document_title` already returned a non-empty title, WordPress
	 * will not enter the title-parts pipeline and this method will never run.
	 *
	 * @since 6.15.17
	 *
	 * @param string   $new_title The Event Category archive title.
	 * @param string   $title     The original title.
	 * @param \WP_Term $cat       The Event Category term used to build the title.
	 *
	 * @return string The filtered title.
	 */
	public function filter_category_title( $new_title, $title, $cat ) {
		$yoast_title = $this->get_yoast_title_for_term( $cat );

		return ! empty( $yoast_title ) ? $yoast_title : $new_title;
	}

	/**
	 * Get the title template from Yoast term meta.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 *
	 * @return string|false The title template, or false if not set.
	 */
	private function get_title_template( WP_Term $term ) {
		if ( ! class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
			return false;
		}

		$title_template = WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, 'title' );

		return ! empty( $title_template ) ? $title_template : false;
	}

	/**
	 * Process a title template through Yoast's variable replacement engine.
	 *
	 * @since 6.15.17
	 *
	 * @param string  $title_template The title template with variable placeholders.
	 * @param WP_Term $term            The term object to use as replacement source.
	 *
	 * @return string|false The processed title, or false on failure.
	 */
	private function process_title_template( string $title_template, WP_Term $term ) {
		if ( ! class_exists( 'WPSEO_Replace_Vars' ) ) {
			return false;
		}

		$replace_vars = new WPSEO_Replace_Vars();
		$processed    = $replace_vars->replace( $title_template, $term );
		$processed    = trim( wp_strip_all_tags( $processed ) );

		return $processed !== '' ? $processed : false;
	}

	/**
	 * Get the fully processed Yoast SEO title for a term.
	 *
	 * Retrieves the custom title template from Yoast's term meta and processes
	 * any variable placeholders (e.g. `%%event_start_date%%`) through Yoast's
	 * `WPSEO_Replace_Vars` engine. The term object is passed as the replacement
	 * source so term-related variables resolve correctly.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 *
	 * @return string|false The processed title, or false if no custom title is set.
	 */
	private function get_yoast_title_for_term( WP_Term $term ) {
		$title_template = $this->get_title_template( $term );
		if ( ! $title_template ) {
			return false;
		}

		// If the title has no variable placeholders, return it directly.
		if ( strpos( $title_template, '%%' ) === false ) {
			$clean = trim( wp_strip_all_tags( $title_template ) );
			return $clean !== '' ? $clean : false;
		}

		// Process variable placeholders through Yoast's replacement engine.
		return $this->process_title_template( $title_template, $term );
	}

	/**
	 * Check if the term is an Event Category.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 *
	 * @return bool True if the term is an Event Category.
	 */
	private function is_event_category( WP_Term $term ): bool {
		return is_tax( TEC_Plugin::TAXONOMY ) && $term->taxonomy === TEC_Plugin::TAXONOMY;
	}

	/**
	 * Check if the term is an Event Tag.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 *
	 * @return bool True if the term is an Event Tag.
	 */
	private function is_event_tag( WP_Term $term ): bool {
		return is_tag()
			&& $term->taxonomy === 'post_tag'
			&& function_exists( 'tribe_is_event_query' )
			&& tribe_is_event_query();
	}

	/**
	 * Check whether the term belongs to a taxonomy this class should handle.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 *
	 * @return bool True if the term is an Event Category or an Event Tag.
	 */
	private function is_supported_taxonomy( WP_Term $term ): bool {
		return $this->is_event_category( $term ) || $this->is_event_tag( $term );
	}
}
