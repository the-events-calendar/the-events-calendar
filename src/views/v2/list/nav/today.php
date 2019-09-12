<?php
/**
 * View: List View Nav Today Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav/today.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the today page, if any, or an empty string.
 *
 * @version 4.9.4
 *
 */

// If we didn't have a view setup we cannot print today's link
if ( ! $this->get( 'view' ) ) {
	return false;
}

$today_url = tribe_events_get_url( [ 'paged' => 1 ], $this->get( 'view' )->get_url() );
?>
<li class="tribe-events-c-nav__list-item tribe-events-c-nav__list-item--today">
	<a
		href="<?php echo esc_url( $link ); ?>"
		class="tribe-events-c-nav__today tribe-common-b2"
		data-js="tribe-events-view-link"
	>
		<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
	</a>
</li>
