<?php
/**
 * Class Tribe__Events__Aggregator__Processes__Batch_Imports
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Aggregator__Processes__Batch_Imports
 *
 * Add custom hooks in order to support batch pushing.
 *
 * @since TBD
 */
class Tribe__Events__Aggregator__Processes__Batch_Imports {
	/**
	 * Update the endpoint used to initiate an process an import of events.
	 *
	 * @param string   $url      The completed URL generated.
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
	 * @since TBD
	 *
	 * @param bool                                        $service_supports_batch_push If the current import has support for batch pushing.
	 * @param Tribe__Events__Aggregator__Record__Abstract $abstract
	 *
	 * @return boolean If the current import supports batch pushing or not.
	 */
	public function allow_batch_import( $service_supports_batch_push, $abstract ) {
		if ( ! $service_supports_batch_push ) {
			return $service_supports_batch_push;
		}

		if ( ! $abstract instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			return $service_supports_batch_push;
		}

		// This is a new record and does not have a parent.
		if ( ! $abstract->post instanceof WP_Post || ! $abstract->post->post_parent ) {
			return $service_supports_batch_push;
		}

		$parent_id = $abstract->post->post_parent;

		// Make sure $parent_id is always an integer.
		if ( $parent_id instanceof WP_Post ) {
			$parent_id = $parent_id->ID;
		}

		$allow_batch_pushing = get_post_meta(
			$parent_id,
			Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'allow_batch_push',
			true
		);

		if ( tribe_is_truthy( $allow_batch_pushing ) ) {
			return $service_supports_batch_push;
		}

		return false;
	}

	/**
	 * Update the args used for imports.
	 *
	 * TODO: Update EventBrite to use batch pushing to deliver events instead.
	 *
	 * @since TBD
	 *
	 * @param array                              $args   Arguments to queue the import.
	 * @param Tribe__Events__Aggregator__Service $record Which record we are dealing with.
	 *
	 * @return mixed
	 */
	public function import_args( $args, $record ) {
		if ( isset( $args['callback'] ) ) {
			$args['callback'] = null;
		}

		return $args;
	}
}
