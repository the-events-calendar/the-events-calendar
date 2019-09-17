<?php
/**
 * View: Events Bar Views List
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/events-bar/views/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<div
	class="tribe-events-c-view-selector__content"
	id="tribe-events-view-selector-content"
	data-js="tribe-events-view-selector-list-container"
>
	<ul class="tribe-events-c-view-selector__list">
		<?php foreach ( $this->get( 'views' ) as $view => $view_class_name ) : ?>
			<?php $this->template( 'components/events-bar/views/list/item', [ 'view_class_name' => $view_class_name ] ); ?>
		<?php endforeach; ?>
	</ul>
</div>
