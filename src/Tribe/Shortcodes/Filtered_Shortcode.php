<?php
class Tribe__Events__Pro__Shortcodes__Filtered_Shortcode {
	protected $filters = array();
	protected $terms = array();


	/**
	 * Sets up an array of taxonomy filters, if required by the shortcode
	 * arguments.
	 */
	protected function taxonomy_filters() {
		// Consolidate plural/singular forms into one
		$params  = array();
		$params['categories'] = $this->arguments['categories'] . ',' . $this->arguments['category'];
		$params['tags'] = $this->arguments['tags'] . ',' . $this->arguments['tag'];

		// Build our taxonomy filter
		foreach ( $this->tax_relationships as $param => $tax ) {
			// Check for taxonomy terms for each supported taxonomy
			$this->terms = explode( ',', $params[ $param ] );
			foreach ( $this->terms as $term ) {
				$this->add_term( $term, $tax );
			}
		}

		// Add the filters to the list of widget arguments
		if ( ! empty( $this->filters ) ) $this->arguments['raw_filters'] = $this->filters;
	}

	/**
	 * Potentially add a taxonomy term to our list of filters.
	 *
	 * @param $term
	 * @param $tax
	 */
	protected function add_term( $term, $tax ) {
		$term = trim( $term );
		if ( empty( $term ) ) return;

		// Accept term IDs - these should be prefixed with a # symbol
		if ( 0 === strpos( $term, '#' ) && is_numeric( substr( $term, 1 ) ) ) {
			$this->filters[ $tax ][] = absint( substr( $term, 1 ) );
		}
		// Also accept term slugs...
		else {
			$term_obj = get_term_by( 'slug', $term, $tax );
			if ( false === $term_obj ) return;
			$this->filters[ $tax ][] = $term_obj->term_id;
		}
	}
}
