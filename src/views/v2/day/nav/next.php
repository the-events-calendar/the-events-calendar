<?php
/**
 * View: Day View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/nav/next.php
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
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="next"
		class="tribe-events-c-nav__next tribe-common-b2"
		data-js="tribe-events-view-link"
	>
		<?php esc_html_e( 'Next Day', 'the-events-calendar' ); ?>
	</a>
</li>
