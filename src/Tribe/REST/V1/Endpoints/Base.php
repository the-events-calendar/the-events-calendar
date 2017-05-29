<?php


abstract class Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function __construct( Tribe__REST__Messages_Interface $messages ) {
		$this->messages = $messages;
	}

	/**
	 * Parses a date value throwing an exception if not valid.
	 *
	 * @param string          $date The date under which the request could store the
	 * @param string          $message_slug
	 *
	 * @return false|string Either the date in the specified format localized to the site timezone or `false`
	 *                      if the date is not provided in the request under the `$key` index.
	 *
	 * @throws Tribe__REST__Exceptions__Exception If the date provided is not a valid date string or
	 *                                            UNIX timestamp.
	 */
	protected function parse_date_value( $date, $message_slug  ) {
		if ( ! empty( $date ) ) {
			$parsed = Tribe__Timezones::localize_date( Tribe__Date_Utils::DBDATETIMEFORMAT, $request[ $key ] );

			if ( false === $parsed ) {
				$message = $this->messages->get_message( $message_slug );

				throw new Tribe__REST__Exceptions__Exception( $key, $message, 400 );
			}

			return $parsed;
		}

		return false;
	}

	/**
	 * Returns the default value of posts per page.
	 *
	 * Cascading fallback is TEC `posts_per_page` option, `posts_per_page` option and, finally, 20.
	 *
	 * @return int
	 */
	protected function get_default_posts_per_page() {
		$posts_per_page = tribe_get_option( 'posts_per_page', get_option( 'posts_per_page' ) );

		return ! empty( $posts_per_page ) ? $posts_per_page : 20;
	}

	/**
	 * Falls back on an allowed post status in respect to the user user capabilities of publishing.
	 *
	 * @param string $post_status
	 * @param string $post_type
	 *
	 * @return string
	 */
		public function scale_back_post_status( $post_status, $post_type ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
			return $post_status;
		}
		if ( in_array( $post_status, array( 'publish', 'future' ) ) ) {
			return 'pending';
		}

		return $post_status;
	}
}