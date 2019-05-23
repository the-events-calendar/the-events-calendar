<?php
/**
 * View: Top Bar - Navigation
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/top-bar/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<div class="tribe-events-c-top-bar__nav-wrapper">
	<nav class="tribe-events-c-top-bar__nav">
		<ul class="tribe-events-c-top-bar__nav-list">
			<li class="tribe-events-c-top-bar__nav-list-item">
				<a
					href="#"
					class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-common-b3 tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
					aria-label="<?php esc_html_e( 'Previous', 'the-events-calendar' ); ?>"
					title="<?php esc_html_e( 'Previous', 'the-events-calendar' ); ?>"
				>
				</a>
			</li>
			<li class="tribe-events-c-top-bar__nav-list-item">
				<a
					href="<?php echo home_url( 'events/list/page/2?view=list' ); /* @todo Fix this link reference */ ?>"
					class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-common-b3 tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
					aria-label="<?php esc_html_e( 'Next', 'the-events-calendar' ); ?>"
					title="<?php esc_html_e( 'Next', 'the-events-calendar' ); ?>"
				>
				</a>
			</li>
		</ul>
	</nav>
</div>
