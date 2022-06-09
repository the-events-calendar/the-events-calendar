<?php
/**
 * View: Month View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the next page, if any, or an empty string.
 * @var string $label The label for the next link.
 *
 * @version 5.3.0
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--next">
	<a
		href="<?php echo esc_url( $link ); ?>"
		rel="next"
		class="tribe-events-c-nav__next tribe-common-b2"
		data-js="tribe-events-view-link"
		aria-label="<?php echo esc_attr( sprintf( __( 'Next month, %1$s', 'the-events-calendar' ), $label ) ); ?>"
		title="<?php echo esc_attr( sprintf( __( 'Next month, %1$s', 'the-events-calendar' ), $label ) ); ?>"
		rel="<?php echo esc_attr( $next_rel ); ?>"
	>
		<?php echo esc_html( $label ); ?>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-nav__next-icon-svg' ] ] ); ?>
	</a>
</li>
