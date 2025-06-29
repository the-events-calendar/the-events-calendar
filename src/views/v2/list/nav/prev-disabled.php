<?php
/**
 * View: List View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @since 5.3.0
 * @since TBD Removed redundant aria-label and title attributes. Visible text is sufficient.
 *
 */

?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2 tribe-common-b1--min-medium"
		disabled
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-events-c-nav__prev-icon-svg' ] ] ); ?>
		<span class="tribe-events-c-nav__prev-label">
			<?php echo wp_kses(
				sprintf(
					/* translators: %s: Event (plural). */
					__( 'Previous %1$s', 'the-events-calendar' ),
					'<span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>'
				)
			); ?>
		</span>
	</button>
</li>
