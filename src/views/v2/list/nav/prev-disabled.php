<?php
/**
 * View: List View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the previous page, if any, or an empty string.
 *
 * @version 4.9.8
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium" disabled>
		<?php
			$events_label = '<span class="tribe-events-c-nav__prev-label-plural"> ' . tribe_get_event_label_plural() . '</span>';
			echo wp_kses(
				/* translators: %s: Event (plural or singular). */
				sprintf( __( 'Previous %1$s', 'the-events-calendar' ), $events_label ),
				[ 'span' => [ 'class' => [] ] ]
			);
		?>
	</button>
</li>
