<?php
/**
 * View: Linked Breadcrumb
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breadcrumbs/breadcrumb.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 * @var array $breadcrumb Data for breadcrumb.
 */
?>
<li class="tribe-events-c-breadcrumbs__list-item">
	<span class="tribe-events-c-breadcrumbs__list-item-text">
		<?php echo esc_html( $breadcrumb['label'] ); ?>
	</span>
	<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-breadcrumbs__list-item-icon-svg' ] ] ); ?>
</li>
