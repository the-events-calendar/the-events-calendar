<?php
/**
 * View: List View Nav Disabled Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/next-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.10
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<button class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium" disabled>
		<?php
			$events_label = '<span class="tribe-events-c-nav__next-label-plural"> ' . tribe_get_event_label_plural() . '</span>';
			echo wp_kses(
				/* translators: %s: Event (plural or singular). */
				sprintf( __( 'Next %1$s', 'the-events-calendar' ), $events_label ),
				[ 'span' => [ 'class' => [] ] ]
			);
		?>
	</button>
</li>
