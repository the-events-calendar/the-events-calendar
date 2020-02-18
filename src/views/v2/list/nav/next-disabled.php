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
 * @version 5.0.1
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<button
		class="tribe-events-c-nav__next tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="<?php echo esc_attr( sprintf( __( 'Next %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>"
		title="<?php echo esc_attr( sprintf( __( 'Next %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>"
		disabled
	>
		<span class="tribe-events-c-nav__next-label">
			<?php
				$events_label = '<span class="tribe-events-c-nav__next-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>';
				echo wp_kses(
					/* translators: %s: Event (plural or singular). */
					sprintf( __( 'Next %1$s', 'the-events-calendar' ), $events_label ),
					[ 'span' => [ 'class' => [] ] ]
				);
			?>
		</span>
	</button>
</li>
