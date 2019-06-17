<?php
/**
 * View: Events Bar Views List
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<ul class="tribe-common-form-control-tabs__list tribe-events-c-events-bar__views-tabs-list" tabindex="-1" role="listbox" aria-activedescendant="tribe-views-list-label">
	<?php foreach ( $this->get( 'views' ) as $view => $view_class_name ) : ?>
		<?php $this->template( 'events-bar/views/item', [ 'view_class_name' => $view_class_name ] ); ?>
	<?php endforeach; ?>
</ul>