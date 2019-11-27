<?php
/**
 * View: Day View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the next page.
 *
 * @version 4.9.10
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="next"
		class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium"
		data-js="tribe-events-view-link"
	>
		<?php esc_html_e( 'Next Day', 'the-events-calendar' ); ?>
	</a>
</li>
