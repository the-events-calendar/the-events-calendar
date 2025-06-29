<?php
/**
 * View: List View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the next page.
 *
 * @version TBD
 *
 * @since 5.3.0
 * @since TBD removed redundant aria-label and title attributes. Visible text is sufficient.
 *
 * @see tribe_get_event_label_plural() For the event label plural.
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
	<span class="tribe-events-c-nav__next-label">
			<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: Event (plural or singular). */
						__( 'Next %1$s', 'tribe-events-calendar-pro' ),
						'<span class="tribe-events-c-nav__next-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>'
					),
					[ 'span' => [ 'class' => [] ] ]
				);
			?>
		</span>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-nav__next-icon-svg' ] ] ); ?>
	</a>
</li>
