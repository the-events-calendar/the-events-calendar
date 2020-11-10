<?php

class Tribe__Events__Aggregator__Processes__Batch_Imports {
	public function hook() {
		add_filter( 'tribe_events_aggregator_build_url', [ $this, 'build_url' ], 10, 3 );
		add_filter( 'tribe_aggregator_service_post_import_args', [ $this, 'import_args' ], 10, 2 );
		add_filter( 'tribe_aggregator_allow_batch_push', [ $this, 'allow_batch_import' ], 10, 2 );
	}

	public function build_url( $url, $endpoint, $api ) {
		if ( 'import' !== $endpoint ) {
			return $url;
		}

		return "{$api->domain}{$api->path}{v2.0.0}/{$endpoint}";
	}

	public function allow_batch_import( $service_supports_batch_push, $abstract ) {
		if ( ! $service_supports_batch_push ) {
			return $service_supports_batch_push;
		}

		if ( ! $abstract instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			return $service_supports_batch_push;
		}

		// This is a new record and does not have a parent.
		if ( ! $abstract->post->post_parent ) {
			return $service_supports_batch_push;
		}

		$parent = $abstract->post->post_parent;

		if ( $parent instanceof WP_Post ) {
			$parent = $parent->ID;
		}

		$batch = get_post_meta(
			$parent,
			Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'allow_batch_push',
			true
		);

		if ( tribe_is_truthy( $batch ) ) {
			return $service_supports_batch_push;
		}

		return false;
	}

	/**
	 * Update the args used for imports.
	 *
	 * TODO: Update EventBrite to use batch pushing to deliver events instead.
	 *
	 * @param $args
	 * @param $record
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
