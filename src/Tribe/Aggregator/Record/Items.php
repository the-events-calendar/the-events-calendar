<?php

/**
 * Class Tribe__Events__Aggregator__Record__Items
 *
 * @since 4.6.16
 */
class Tribe__Events__Aggregator__Record__Items {
	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var array
	 */
	protected $original_items;

	/**
	 * Tribe__Events__Aggregator__Record__Items constructor.
	 *
	 * @since 4.6.16
	 *
	 * @param array $items A list of items to process, the format should be the one used by EA Service
	 */
	public function __construct( array $items = [] ) {
		$this->items          = $items;
		$this->original_items = $items;
	}

	/**
	 * Returns the items as modified by the class.
	 *
	 * @since 4.6.16
	 *
	 * @return array
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Resets, or sets, the items the class should handle.
	 *
	 * @since 4.6.16
	 *
	 * @param array $items
	 */
	public function set_items( array $items ) {
		$this->items          = $items;
		$this->original_items = $items;
	}

	/**
	 * Parses the items to find those that depend on linked posts defined by other items
	 * and marks them as dependent.
	 *
	 * @since 4.6.16
	 *
	 * @return $this
	 */
	public function mark_dependencies() {
		$items = $this->items;

		$venue_global_ids     = [];
		$organizer_global_ids = [];

		foreach ( $items as &$item ) {
			$item             = (object) $item;
			$item->depends_on = [];

			if ( isset( $item->venue ) ) {
				$venue = (object) $item->venue;
				if ( ! isset( $venue->global_id ) ) {
					continue;
				}
				$venue_global_id = $venue->global_id;
				if ( in_array( $venue_global_id, $venue_global_ids, true ) ) {
					$item->depends_on[] = $venue_global_id;
				} else {
					$venue_global_ids[] = $venue_global_id;
				}
			}

			if ( isset( $item->organizer ) ) {
				$organizers = $item->organizer;

				if ( is_object( $item->organizer ) ) {
					$organizers = [ $item->organizer ];
				}

				foreach ( $organizers as $organizer ) {
					$organizer = (object) $organizer;
					if ( ! isset( $organizer->global_id ) ) {
						continue;
					}
					$organize_global_id = $organizer->global_id;
					if ( in_array( $organize_global_id, $organizer_global_ids, true ) ) {
						$item->depends_on[] = $organize_global_id;
					} else {
						$organizer_global_ids[] = $organize_global_id;
					}
				}
			}
			if ( empty( $item->depends_on ) ) {
				unset( $item->depends_on );
			}
		}

		$this->items = $items;

		return $this;
	}

	/**
	 * Returns the items originally set via the constructor the `set_items` method.
	 *
	 * @since 4.6.16
	 *
	 * @return array
	 */
	public function get_original_items() {
		return $this->original_items;
	}
}

