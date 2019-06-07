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
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-top-bar__nav-wrapper">
	<nav class="tribe-events-c-top-bar__nav">
		<ul class="tribe-events-c-top-bar__nav-list">
			<?php $this->template( 'top-bar/nav/prev', [ 'link' => $prev_url ] ); ?>
			<?php $this->template( 'top-bar/nav/next', [ 'link' => $next_url ] ); ?>
		</ul>
	</nav>
</div>
