<?php
/**
 * View: Top Bar Navigation Next Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/top-bar/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $next_url The URL to the next page, if any, or an empty string.
 *
 * @version 5.0.1
 *
 */
$label = sprintf( __( 'Next %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $next_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
		data-js="tribe-events-view-link"
	>
	</a>
</li>
