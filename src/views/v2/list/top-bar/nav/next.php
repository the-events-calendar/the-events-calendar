<?php
/**
 * View: Top Bar Navigation Next Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/top-bar/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $next_url The URL to the next page, if any, or an empty string.
 *
 * @version TBD
 *
 * @since 5.3.0
 * @since TBD Removed redundant aria-label attribute. Title attribute is sufficient.
 *
 */
$label = sprintf( __( 'Next %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $next_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		title="<?php echo esc_attr( $label ); ?>"
		data-js="tribe-events-view-link"
	>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-common-c-btn-icon__icon-svg', 'tribe-events-c-top-bar__nav-link-icon-svg' ] ] ); ?>
	</a>
</li>
