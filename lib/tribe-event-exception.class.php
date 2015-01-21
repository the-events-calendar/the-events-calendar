<?php
/**
 * Exception handling for third-party plugins dealing with the post edit view.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribeEventsPostException' ) ) {
	class TribeEventsPostException extends Exception {
		/**
		 * Display the exception message in the div #tribe-events-post-error
		 *
		 * @param int $post ->ID
		 */
		public static function displayMessage( $postId ) {
			if ( $error = get_post_meta( $postId, TribeEvents::EVENTSERROROPT, true ) ) : ?>
				<script type="text/javascript">
					jQuery('#tribe-events-post-error')
						.append('<h3><?php _e( 'Error', 'tribe-events-calendar' ); ?></h3><p>' + '<?php echo $error; ?>' + '</p>')
						.show();
				</script>
			<?php endif;
		}
	} // end TribeEventsPostException
} // end if !class_exists TribeEventsPostException

/**
 * Exception handling for third-party plugins dealing with the WordPress options view.
 */
if ( ! class_exists( 'TribeEventsOptionsException' ) ) {
	class TribeEventsOptionsException extends Exception {
		/**
		 * Display the exception message in the div #tribe-events-options-error
		 */
		public static function displayMessage() {
			$eventsOptions = get_option( TribeEvents::OPTIONNAME, array() );
			if ( isset( $eventsOptions['error'] ) && $eventsOptions['error'] ) : ?>
				<script type="text/javascript">jQuery('#tribe-events-options-error').append('<h3><?php _e( 'Error', 'tribe-events-calendar' ); ?></h3><p>' + '<?php echo $eventsOptions['error']; ?>' + '</p>').show();</script>
			<?php endif;
		}
	} // end TribeEventsOptionsException
} // end if !class_exists TribeEventsOptionsException
