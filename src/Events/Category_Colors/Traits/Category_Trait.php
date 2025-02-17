<?php
/**
 * Trait for handling category colors and metadata retrieval.
 * This trait is responsible for fetching category metadata, processing the data,
 * and providing structured output for category colors.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Traits
 */

namespace TEC\Events\Category_Colors\Traits;

use TEC\Common\StellarWP\DB\DB;
use TEC\Events\Category_Colors\Category\Events_Category;
use Tribe__Events__Main;

/**
 * Provides methods for retrieving and processing category colors and metadata.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Traits
 */
trait Category_Trait {
	/**
	 * Stores the fetched category metadata.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $categories = [];

	/**
	 * Meta keys used for retrieving category colors.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $meta_keys = [
		'tec-events-cat-colors-primary',
		'tec-events-cat-colors-secondary',
		'tec-events-cat-colors-text',
		'tec-events-cat-colors-priority',
	];

	/**
	 * Fetch category meta for the `tribe_events_cat` taxonomy.
	 * Retrieves metadata from the database in batches and organizes it by term ID.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function fetch_category_meta(): void {
		$db     = tribe( DB::class );
		$offset = 0;

		/**
		 * Filter the batch size for category color meta queries.
		 *
		 * @since TBD
		 *
		 * @param int $batch_size The number of records to fetch per batch. Default is 500.
		 *
		 * @return int The filtered batch size.
		 */
		$batch_size = (int) apply_filters( 'tec_events_category_color_generator_batch_size', 500 );

		/**
		 * Filter the meta keys used in category color queries.
		 *
		 * @since TBD
		 *
		 * @param array $meta_keys The array of meta keys to retrieve.
		 *
		 * @return array The filtered meta key array.
		 */
		$this->meta_keys = (array) apply_filters( 'tec_events_category_color_generator_meta_keys', $this->meta_keys );

		do {
			$results = $db->table( 'term_taxonomy', 'tt' )
				->select( 'tm.term_id', 'tm.meta_key', 'tm.meta_value' )
				->innerJoin( 'termmeta', 'tt.term_id', 'tm.term_id', 'tm' )
				->where( 'tt.taxonomy', Tribe__Events__Main::TAXONOMY )
				->whereIn( 'tm.meta_key', $this->meta_keys )
				->limit( $batch_size )
				->offset( $offset )
				->getAll();

			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$term_id    = (int) $row->term_id;
					$meta_key   = $row->meta_key;
					$meta_value = $row->meta_value;

					if ( ! isset( $this->categories[ $term_id ] ) ) {
						$this->categories[ $term_id ] = [
							'primary'   => '',
							'secondary' => '',
							'text'      => '',
							'priority'  => -1,
						];
					}

					switch ( $meta_key ) {
						case 'tec-events-cat-colors-primary':
							$this->categories[ $term_id ]['primary'] = $meta_value;
							break;
						case 'tec-events-cat-colors-secondary':
							$this->categories[ $term_id ]['secondary'] = $meta_value;
							break;
						case 'tec-events-cat-colors-text':
							$this->categories[ $term_id ]['text'] = $meta_value;
							break;
						case 'tec-events-cat-colors-priority':
							$this->categories[ $term_id ]['priority'] = is_numeric( $meta_value ) ? (int) $meta_value : -1;
							break;
					}
				}
			}

			$offset += $batch_size;
		} while ( ! empty( $results ) ); // Continue until no more results.
	}

	/**
	 * Process fetched data, instantiate category objects, and structure them.
	 * If a term ID is provided, it returns the structured data for that single category.
	 * Otherwise, it processes all fetched categories and returns an array of structured data.
	 *
	 * @since TBD
	 *
	 * @param int|null $term_id Optional term ID. If provided, returns only that category.
	 *
	 * @return array Structured category color data.
	 */
	public function build_category_structure( ?int $term_id = null ): array {
		$structured_data = [];

		if ( null !== $term_id && isset( $this->categories[ $term_id ] ) ) {
			$category = new Events_Category( $term_id, $this->categories[ $term_id ] );

			return $category->get_schema();
		}

		foreach ( $this->categories as $term_id => $data ) {
			$category          = new Events_Category( $term_id, $data );
			$structured_data[] = $category->get_schema();
		}

		/**
		 * Filter the final structured category color data.
		 *
		 * @since TBD
		 *
		 * @param array $structured_data The structured array of categories.
		 */
		return (array) apply_filters( 'tec_events_category_color_generator_final_data', $structured_data );
	}
}
