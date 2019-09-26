<?php
/**
 * View: Loader
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/loader.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<div
	class="tribe-events-view-loader tribe-common-a11y-hidden"
	role="alert"
	aria-live="assertive"
>
	<div class="tribe-events-view-loader__spinner">
		<?php echo esc_html( $text ); ?>
	</div>
</div>
