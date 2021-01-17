<?php
/**
 * View: Top Bar Navigation Previous Disabled Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/top-bar/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 */
$label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
		disabled
	>
		<?php $this->template( 'components/icons/caret-left', [ 'classes' => [ 'tribe-common-c-btn-icon__icon-svg', 'tribe-events-c-top-bar__nav-link-icon-svg' ] ] ); ?>
	</button>
</li>
