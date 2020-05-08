<?php
/**
 * View: List View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.1.1
 */

/* translators: %s: Event (plural or singular). */
$label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );

/* translators: %s: Event (plural or singular). */
$events_mobile_friendly_label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), '<span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>' );
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
		disabled
	>
		<span class="tribe-events-c-nav__prev-label">
			<?php echo wp_kses( $events_mobile_friendly_label, [ 'span' => [ 'class' => [] ] ] ); ?>
		</span>
	</button>
</li>
