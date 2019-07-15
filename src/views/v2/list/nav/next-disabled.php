<?php
/**
 * View: List View Nav Disabled Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/next-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the next page, if any, or an empty string.
 *
 * @version 4.9.4
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<button class="tribe-events-c-nav__next tribe-common-b2" disabled>
		<?php echo esc_html( sprintf( __( 'Next %s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>
	</button>
</li>
