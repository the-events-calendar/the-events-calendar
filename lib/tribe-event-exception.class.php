<?php
/**
 * Exception handling for third-party plugins dealing with the post edit view.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TEC_Post_Exception' ) ) {
	class TEC_Post_Exception extends Exception {
		/**
		* Display the exception message in the div #tec-post-error
		* @param int $post->ID
		*/
		public function displayMessage( $postId ) {
			if( $error = get_post_meta( $postId, Events_Calendar_Pro::EVENTSERROROPT, true ) ) : ?>
				<script type="text/javascript">jQuery('#tec-post-error').append('<h3>Error</h3><p>' + '<?php echo $error; ?>' + '</p>').show();</script>
			<?php endif;
		}
	} // end TEC_Post_Exception
} // end if !class_exists TEC_Post_Exception

/**
 * Exception handling for third-party plugins dealing with the Wordpress options view.
 */
if( !class_exists( 'TEC_WP_Options_Exception' ) ) {
	class TEC_WP_Options_Exception extends Exception {
		/**
		* Display the exception message in the div #tec-options-error
		*/
		public function displayMessage() {
			$eventsOptions = get_option(Events_Calendar_Pro::OPTIONNAME, array() );
			if( $eventsOptions['error'] ) : ?>
				<script type="text/javascript">jQuery('#tec-options-error').append('<h3>Error</h3><p>' + '<?php echo $eventsOptions['error']; ?>' + '</p>').show();</script>
			<?php endif;
	    }
	} // end TEC_WP_Options_Exception
} // end if !class_exists TEC_WP_Options_Exception
?>