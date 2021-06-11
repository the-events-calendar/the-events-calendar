<?php

namespace Tribe\Events\Aggregator\Processes;

use stdClass;
use Tribe__Events__Aggregator__Record__Abstract as Record_Abstract;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Aggregator__Service;
use WP_Post;

/**
 * Class Batch_Imports
 *
 * @since 5.3.0
 */

/**
 * Class Batch_Imports
 *
 * Add custom hooks in order to support batch pushing.
 *
 * @since 5.3.0
 */
class Batch_Imports {
	/**
	 * Update the endpoint used to initiate a process an import of events.
	 *
	 * @param string   $url      The input, generated URL.
	 * @param string   $endpoint The path of the endpoint inside of the base url.
	 * @param stdClass $api      An object representing the properties of the API.
	 *
	 * @return string The modified URL where to hit to process an import.
	 */
	public function build_url( $url, $endpoint, $api ) {
		if ( 'import' !== $endpoint ) {
			return $url;
		}

		return $api->domain . $api->path . 'v2.0.0' . '/' . $endpoint;
	}

	/**
	 * Filter imports (if it has a parent import is a schedule import) and if the parent was not a batch pushing import,
	 * make sure that that setting is respected, in this way we can support backwards compatibility as all imports created
	 * before batch pushing are going to remaining using the old system and new imports are going to be considered as
	 * batch pushing imports.
	 *
	 * @since 5.3.0
	 *
	 * @param bool            $service_supports_batch_push If the current import has support for batch pushing.
	 * @param Record_Abstract $abstract
	 *
	 * @return boolean If the current import supports batch pushing or not.
	 */
	public function allow_batch_import( $service_supports_batch_push, $abstract ) {
		if ( ! $service_supports_batch_push ) {
			return $service_supports_batch_push;
		}

		if ( 'async' === tribe_get_option( 'tribe_aggregator_import_process_system' ) ) {
			return false;
		}

		if ( ! $abstract instanceof Record_Abstract ) {
			return $service_supports_batch_push;
		}

		// This is a new record and does not have a parent.
		if ( ! $abstract->post instanceof WP_Post || ! $abstract->post->post_parent ) {
			return $service_supports_batch_push;
		}

		$parent_id = $abstract->post->post_parent instanceof WP_Post
			? $abstract->post->post_parent->ID
			: $abstract->post->post_parent;

		$parent_record = Records::instance()->get_by_post_id( $parent_id);

		// Only return the $service_supports_batch_push if the parent record was created with batch pushing.
		if ( $parent_record instanceof Record_Abstract && ! $parent_record->is_polling() ) {
			return $service_supports_batch_push;
		}

		return false;
	}
}
