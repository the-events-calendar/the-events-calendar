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
 * @version TBD
 *
 */
tribe_events_get_url( [ 'paged' => 1 ], $view->get_url() );
?>
<div class="tribe-events-c-top-bar__today">
	<a
		href="<?php echo esc_url( tribe_events_get_url( [ 'paged' => 1 ], $view->get_url() ) ); ?>"
		class="tribe-common-c-btn-border tribe-events-c-top-bar__today-button"
		data-js="tribe-events-view-link"
	>
		<?php esc_html_e( 'Today', 'the-events-calendar' ); ?>
	</a>

	<span class="tribe-common-h3 tribe-common-h3--alt tribe-events-c-top-bar__today-title">
		<?php esc_html_e( 'Now', 'the-events-calendar' ); ?> &mdash; <time datetime="<?php echo esc_attr( date( 'Y-m-d', time() ) ); ?>"><?php echo date( 'F jS, Y', time() ); ?></time>
	</span>
</div>
