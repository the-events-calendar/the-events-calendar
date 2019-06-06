<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-top-bar__today">
	<a href="#" class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button">
		<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
	</a>

	<span class="tribe-common-h3 tribe-common-h3--alt tribe-events-c-top-bar__today-title">
		<?php esc_html_e( 'Now', 'the-events-calendar' ); ?> &mdash; <time datetime="<?php echo esc_attr( date( 'Y-m-d', time() ) ); ?>"><?php echo date( 'F jS, Y', time() ); ?></time>
	</span>
</div>
