<?php
/**
 * View: List View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

if ( ! tribe_has_previous_event() || ! tribe_has_next_event() ) {
	return;
}

?>
<nav class="tribe-common-c-nav">
	<ul class="tribe-common-c-nav__list">
		<?php $this->template( 'list/nav/prev', [ 'link' => tribe_get_listview_prev_link() ] ); ?>
		<?php $this->template( 'list/nav/next', [ 'link' => tribe_get_listview_next_link() ] ); ?>
	</ul>
</nav>
