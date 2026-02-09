<?php
/**
 * Handles title replacement for Event Categories and Tags to use Yoast SEO titles.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Tribe__Events__Main as TEC_Plugin;
use WP_Term;
use Yoast\WP\SEO\Presentations\Indexable_Presentation;

/**
 * Class Events_Title
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_Title {

	/**
	 * Register the title hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		// Hook into tribe_events_views_v2_category_title to replace TEC's title with Yoast's title.
		add_filter( 'tribe_events_views_v2_category_title', [ $this, 'filter_category_title' ], 10, 5 );
		
		// Also hook into pre_get_document_title at priority 15 to return Yoast title directly.
		add_filter( 'pre_get_document_title', [ $this, 'pre_get_document_title' ], 15 );
	}

	/**
	 * Filter the Event Category title to use Yoast's title if available.
	 *
	 * @since TBD
	 *
	 * @param string    $new_title The Event Category archive title.
	 * @param string    $title     The original title.
	 * @param \WP_Term  $cat       The Event Category term used to build the title.
	 * @param boolean   $depth     Whether to display the taxonomy hierarchy as part of the title.
	 * @param string    $separator The separator character for the title parts.
	 *
	 * @return string The filtered title.
	 */
	public function filter_category_title( $new_title, $title, $cat, $depth, $separator ) {
		// Check if Yoast SEO is available.
		if ( ! function_exists( 'YoastSEO' ) ) {
			return $new_title;
		}

		// Check if Yoast has a custom title set for this term.
		$term_meta = \WPSEO_Taxonomy_Meta::get_term_meta( $cat, $cat->taxonomy, 'title' );

		if ( empty( $term_meta ) ) {
			return $new_title;
		}

		// Get Yoast's processed title.
		$yoast_title = $this->get_yoast_processed_title( $cat, $term_meta );

		// If we have a Yoast title, return it instead of TEC's built title.
		if ( ! empty( $yoast_title ) ) {
			return $yoast_title;
		}

		return $new_title;
	}

	/**
	 * Get Yoast's processed title for a term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $term      The term object.
	 * @param string  $term_meta The term meta title template.
	 *
	 * @return string|false The processed title, or false if not available.
	 */
	private function get_yoast_processed_title( $term, $term_meta ) {
		if ( empty( $term_meta ) ) {
			return false;
		}

		// If the title has no variables, we can return it directly (after basic cleanup).
		if ( strpos( $term_meta, '%%' ) === false ) {
			$clean_title = trim( strip_tags( $term_meta ) );
			return ! empty( $clean_title ) ? $clean_title : false;
		}

		$yoast = YoastSEO();
		if ( ! $yoast ) {
			return false;
		}

		// Get the meta tags context.
		$context = $yoast->meta->for_current_page();
		
		if ( ! $context || ! isset( $context->presentation ) ) {
			return false;
		}

		// Get WPSEO_Replace_Vars to process the title template.
		$replace_vars = null;
		if ( isset( $yoast->classes ) && is_object( $yoast->classes ) && method_exists( $yoast->classes, 'get' ) ) {
			$replace_vars = $yoast->classes->get( 'WPSEO_Replace_Vars' );
		} else {
			$replace_vars = \Yoast\WP\SEO\WordPress\Wrapper::get_replace_vars();
		}

		if ( ! $replace_vars ) {
			return false;
		}

		// Process the title template through Yoast's variable replacement system.
		$source = $context->presentation->source ?? [];
		$yoast_title = $replace_vars->replace( $term_meta, $source );

		// Apply the same filters that Yoast applies to the title.
		$yoast_title = apply_filters( 'wpseo_title', $yoast_title, $context->presentation );
		
		// Strip tags and trim, just like Yoast does.
		$yoast_title = $yoast->helpers->string->strip_all_tags( $yoast_title );
		$yoast_title = trim( $yoast_title );

		return ! empty( $yoast_title ) ? $yoast_title : false;
	}

	/**
	 * Intercept the document title to use Yoast's title for Event Categories and Tags.
	 *
	 * @since TBD
	 *
	 * @param string $title The current title.
	 *
	 * @return string The title, potentially from Yoast.
	 */
	public function pre_get_document_title( $title ) {
		// Only process on the frontend.
		if ( is_admin() ) {
			return $title;
		}

		// Get the term.
		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return $title;
		}

		// Check if we're on an Event Category or Event Tag archive.
		$is_event_category = is_tax( TEC_Plugin::TAXONOMY ) && $term->taxonomy === TEC_Plugin::TAXONOMY;
		$is_event_tag      = is_tag() && $term->taxonomy === 'post_tag' && function_exists( 'tribe_is_event_query' ) && tribe_is_event_query();

		if ( ! $is_event_category && ! $is_event_tag ) {
			return $title;
		}

		// Check if Yoast SEO is available.
		if ( ! function_exists( 'YoastSEO' ) ) {
			return $title;
		}

		// Check if Yoast has a custom title set for this term.
		$term_meta = \WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, 'title' );

		if ( empty( $term_meta ) ) {
			return $title;
		}

		// Get Yoast's processed title.
		$yoast_title = $this->get_yoast_processed_title( $term, $term_meta );

		// If we have a Yoast title, return it to prevent TEC from building its own.
		if ( ! empty( $yoast_title ) ) {
			return $yoast_title;
		}

		return $title;
	}
}
