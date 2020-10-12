<?php
/**
 * Widget: Events List
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/events-list.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version TBD
 *
 * @var array    $events             The array containing the events.
 * @var string   $rest_url           The REST URL.
 * @var string   $rest_nonce         The REST nonce.
 * @var int      $should_manage_url  int containing if it should manage the URL.
 * @var string[] $container_classes  Classes used for the container of the view.
 * @var array    $container_data     An additional set of container `data` attributes.
 * @var string   $breakpoint_pointer String we use as pointer to the current view we are setting up with breakpoints.
 */
?>
<div
	<?php tribe_classes( $container_classes ); ?>
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( $rest_nonce ); ?>"
	data-view-rest-url="<?php echo esc_url( $rest_url ); ?>"
	data-view-manage-url="<?php echo esc_attr( $should_manage_url ); ?>"
	<?php foreach ( $container_data as $key => $value ) : ?>
		data-view-<?php echo esc_attr( $key ) ?>="<?php echo esc_attr( $value ) ?>"
	<?php endforeach; ?>
	<?php if ( ! empty( $breakpoint_pointer ) ) : ?>
		data-view-breakpoint-pointer="<?php echo esc_attr( $breakpoint_pointer ); ?>"
	<?php endif; ?>
>
	<div class="tribe-events-widget-events-list">
		<header class="tribe-events-widget-events-list__header">
			<h3 class="tribe-events-widget-events-list__header-title"><?php // title ?></h3>
		</header>

		<?php if ( ! empty( $events ) ) : ?>
			<?php foreach ( $events as $event ) : ?>
				<?php // include event template ?>
			<?php endforeach; ?>
		<?php else : ?>
			<?php // get messages component ?>
		<?php endif; ?>
	</div>
</div>
