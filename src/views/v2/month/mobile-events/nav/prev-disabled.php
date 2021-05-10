<?php
/**
 * View: Month View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/mobile-events/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $label The label for the previous link.
 *
 * @version 5.3.0
 *
 */
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--prev">
	<button
		class="tribe-events-c-nav__prev tribe-common-b2"
		aria-label="<?php echo esc_attr( sprintf( __( 'Previous month, %1$s', 'the-events-calendar' ), $label ) ); ?>"
		title="<?php echo esc_attr( sprintf( __( 'Previous month, %1$s', 'the-events-calendar' ), $label ) ); ?>"
		disabled
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-events-c-nav__prev-icon-svg' ] ] ); ?>
		<?php echo esc_html( $label ); ?>
	</button>
</li>
