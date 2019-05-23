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
<div class="tribe-events__top-bar-nav-wrapper">
	<nav class="tribe-events__top-bar-nav">
		<ul>
			<li class="tribe-events__top-bar-nav-prev">
				<a
					href="#"
					class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-common-b3 tribe-events-navigation-link"
				></a>
			</li>
			<li class="tribe-events__top-bar-nav-next">
				<a
					href="<?php echo home_url( 'events/list/page/2' ); /* @todo Fix this link reference */ ?>"
					class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-common-b3 tribe-events-navigation-link"
				></a>
			</li>
		</ul>
	</nav>
</div>