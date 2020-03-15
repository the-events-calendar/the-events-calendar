<?php
/**
 * View: List View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the previous page.
 *
 * @version 5.0.1
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="prev"
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>"
		title="<?php echo esc_attr( sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>"
	>
		<span class="tribe-events-c-nav__prev-label">
			<?php
				$events_label = '<span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>';
				echo wp_kses(
					/* translators: %s: Event (plural or singular). */
					sprintf( __( 'Previous %1$s', 'the-events-calendar' ), $events_label ),
					[ 'span' => [ 'class' => [] ] ]
				);
			?>
		</span>
	</a>
</li>
