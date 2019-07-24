<?php
/**
 * View: Top Bar - Today
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */

// If we didn't have a view setup we cannot print today's link
if ( ! $this->get( 'view' ) ) {
	return false;
}

$today_url = tribe_events_get_url( [ 'paged' => 1 ], $this->get( 'view' )->get_url() );
?>
<a
	href="<?php echo esc_url( $today_url ); ?>"
	class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button"
	data-js="tribe-events-view-link"
>
	<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
</a>
