<?php
/**
 * TEC tag for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Tags
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Tags;

use TEC\Common\REST\TEC\V1\Abstracts\Tag;

/**
 * TEC tag for the TEC REST API.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Tags
 */
class TEC_Tag extends Tag {
	/**
	 * Returns the name of the tag.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'Events';
	}

	/**
	 * Returns the tag.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get(): array {
		return [
			'name'        => $this->get_name(),
			'description' => __( 'These operations are introduced by the Events Calendar.', 'the-events-calendar' ),
		];
	}
}
