<?php
/**
 * View: List View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the next page, if any, or an empty string.
 *
 * @version 4.9.8
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
		<?php
			$events_label = '<span class="tribe-events-c-nav__next-label-plural"> ' . tribe_get_event_label_plural() . '</span>';
			echo wp_kses(
				/* translators: %s: Event (plural or singular). */
				sprintf( __( 'Next %1$s', 'the-events-calendar' ), $events_label ),
				[ 'span' => [ 'class' => [] ] ]
			);
		?>
	</a>
</li>
