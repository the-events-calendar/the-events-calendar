<?php
/**
 * View: List View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the previous page.
 *
 * @version 5.3.0
 *
 */

/* translators: %s: Event (plural or singular). */
$label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );

/* translators: %s: Event (plural or singular). */
$events_mobile_friendly_label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), '<span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>' );
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="prev"
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-events-c-nav__prev-icon-svg' ] ] ); ?>
		<span class="tribe-events-c-nav__prev-label">
			<?php
			echo wp_kses(
				$events_mobile_friendly_label,
				[ 'span' => [ 'class' => [] ] ]
			);
			?>
		</span>
	</a>
</li>
