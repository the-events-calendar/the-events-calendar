<?php
/**
 * View: Month View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the previous page, if any, or an empty string.
 * @var string $label The label for the previous link.
 *
 * @version 5.3.0
 *
 * @since 5.3.0
 * @since TBD Removed redundant aria-label attribute. Title attribute is sufficient.
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="prev"
		class="tribe-events-c-nav__prev tribe-common-b2"
		data-js="tribe-events-view-link"
		rel="<?php echo esc_attr( $prev_rel ); ?>"
		title="<?php
			echo esc_attr(
				sprintf(
					/* Translators: %1$s: Event (plural). */
					__( 'Previous month, %1$s', 'the-events-calendar' ),
					$label
				)
			);
		?>"
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-events-c-nav__prev-icon-svg' ] ] ); ?>
		<?php echo esc_html( $label ); ?>
	</a>
</li>
