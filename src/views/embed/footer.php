<?php
/**
 * Embed Footer Template
 *
 * The footer template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/footer.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="wp-embed-footer">
	<?php the_embed_site_title() ?>

	<div class="wp-embed-meta">
		<?php
		/**
		 * Print additional meta content in the embed template.
		 *
		 * @since 4.4.0
		 */
		do_action( 'embed_content_meta' );
		?>
	</div>
</div>
