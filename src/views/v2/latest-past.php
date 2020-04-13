<?php
/**
 * View: Latest Past View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/latest-past.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var array    $events               The array containing the events.
 * @var string   $rest_url             The REST URL.
 * @var string   $rest_nonce           The REST nonce.
 * @var int      $should_manage_url    int containing if it should manage the URL.
 * @var bool     $disable_event_search Boolean on whether to disable the event search.
 * @var string[] $container_classes    Classes used for the container of the view.
 * @var array    $container_data       An additional set of container `data` attributes.
 * @var string   $breakpoint_pointer   String we use as pointer to the current view we are setting up with breakpoints.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}
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
	<div class="tribe-events-latest-past-container">
		<?php $this->template( 'components/loader', [ 'text' => __( 'Loading...', 'the-events-calendar' ) ] ); ?>

		<div class="tribe-events-calendar-latest-past">

			<?php $this->template( 'latest-past/heading' ); ?>

			<?php foreach ( $events as $event ) : ?>
				<?php $this->setup_postdata( $event ); ?>

				<?php $this->template( 'latest-past/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

	</div>

</div>
