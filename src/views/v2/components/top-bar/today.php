<?php
/**
 * View: Top Bar - Today
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $today_url   The URL to the today page.
 * @var string $today_title The string used for the aria-label of the button.
 * @var string $today_label The string used for the visible button text.
 *
 * @version 5.2.0
 * @since TBD Removed redundant title attribute.
 * @since TBD Only show the aria-label if it's different from the label. Otherwise it's redundant.
 *
 */
?>
<a
	href="<?php echo esc_url( $today_url ); ?>"
	class="tribe-common-c-btn-border-small tribe-events-c-top-bar__today-button tribe-common-a11y-hidden"
	data-js="tribe-events-view-link"
	<?php if ( $today_label !== $today_title ) : ?>
		aria-label="<?php echo esc_attr( $today_title ); ?>"
	<?php endif; ?>
>
	<?php echo esc_html( $today_label ); ?>
</a>
