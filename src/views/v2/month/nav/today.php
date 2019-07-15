<?php
/**
 * View: Month View Nav Today Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/nav/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the today page, if any, or an empty string.
 *
 * @version 4.9.4
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--today">
	<a class="tribe-events-c-nav__today tribe-common-b2" href="<?php echo esc_url( $link ); ?>">
		<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
	</a>
</li>
