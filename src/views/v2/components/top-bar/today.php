<?php
/**
 * View: Top Bar - Today
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $today_url The URL to the today page.
 *
 * @version 5.0.1
 */
?>
<a
	href="<?php echo esc_url( $today_url ); ?>"
	class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button tribe-common-a11y-hidden"
	data-js="tribe-events-view-link"
	aria-label="<?php esc_attr_e( 'Click to select today\'s date', 'the-events-calendar' ); ?>"
	title="<?php esc_attr_e( 'Click to select today\'s date', 'the-events-calendar' ); ?>"
>
	<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
</a>
