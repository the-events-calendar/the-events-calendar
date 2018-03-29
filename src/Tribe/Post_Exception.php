<?php
/**
 * Exception handling for third-party plugins dealing with the post edit view.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Post_Exception' ) ) {
	class Tribe__Events__Post_Exception extends Exception {
		/**
		 * Display the exception message in the div #tribe-events-post-error
		 *
		 * @param int $post ->ID
		 */
		public static function displayMessage( $postId ) {
			if ( $error = get_post_meta( $postId, Tribe__Events__Main::EVENTSERROROPT, true ) ) : ?>
				<script>
					jQuery('#tribe-events-post-error')
						.append('<h2><?php esc_html_e( 'Error', 'the-events-calendar' ); ?></h2><p>' + '<?php echo $error; ?>' + '</p>')
						.show();
				</script>
			<?php endif;
		}
	} // end Tribe__Events__Post_Exception
} // end if !class_exists Tribe__Events__Post_Exception
