<?php

/**
 * Class Tribe__Events__Pro__Recurrence_Permalinks
 */
class Tribe__Events__Pro__Recurrence_Permalinks {

	public function filter_recurring_event_permalinks( $post_link, $post, $leavename, $sample ) {
		// URL Arguments on home_url() pre-check
		$url_query = @parse_url( $post_link, PHP_URL_QUERY );
		$url_args = wp_parse_args( $url_query, array() );
		$permalink_structure = get_option( 'permalink_structure' );

		// Remove the "args"
		if ( ! empty( $url_query ) && '' !== $permalink_structure ) {
			$post_link = str_replace( '?' . $url_query, '', $post_link );
		}

		if ( ! $this->should_filter_permalink( $post, $sample ) ) {
			return $post_link;
		}

		$permastruct = $this->get_permastruct( $post );
		if ( $leavename && empty( $post->post_parent ) ) {
			$date = 'all'; // sample permalink for the series
		} else {
			$date = $this->get_date_string( $post );
		}
		$parent = $this->get_primary_event( $post );
		$slug   = $parent->post_name;

		if ( '' === $permalink_structure ) {
			$post_link = remove_query_arg( Tribe__Events__Main::POSTTYPE, $post_link );
			$post_link = add_query_arg( array(
				Tribe__Events__Main::POSTTYPE => $slug,
				'eventDate'           => $date,
			), $post_link );
		} elseif ( ! empty( $permastruct ) ) {
			if ( ! $leavename ) {
				$post_link = str_replace( "%$post->post_type%", $slug, $permastruct );
			}
			$post_link = trailingslashit( $post_link ) . $date;
			$post_link = str_replace( array( home_url( '/' ), site_url( '/' ) ), '', $post_link );
			$post_link = home_url( user_trailingslashit( $post_link ) );
		}

		// Add the Arguments back
		$post_link = add_query_arg( $url_args, $post_link );

		return $post_link;
	}

	protected function should_filter_permalink( $post, $sample ) {
		if ( $post->post_type != Tribe__Events__Main::POSTTYPE ) {
			return false;
		}

		if ( ! tribe_is_recurring_event( $post->ID ) ) {
			return false;
		}

		$unpublished = isset( $post->post_status ) && in_array( $post->post_status, array(
					'draft',
					'pending',
					'auto-draft',
				) );

		if ( $unpublished && ! $sample ) {
			return false;
		}

		return true;
	}

	protected function get_date_string( $post ) {
		$date = get_post_meta( $post->ID, '_EventStartDate', true );
		$date = date( 'Y-m-d', strtotime( $date ) );

		return $date;
	}

	protected function get_primary_event( $post ) {
		while ( ! empty( $post->post_parent ) ) {
			$post = get_post( $post->post_parent );
		}

		return $post;
	}

	protected function get_permastruct( $post ) {
		global $wp_rewrite;
		$permastruct = $wp_rewrite->get_extra_permastruct( $post->post_type );

		return $permastruct;
	}
}

