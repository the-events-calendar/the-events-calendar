<?php
/**
 * Widget: Events List
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/widgets/widget-events-list.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @since 5.3.0
 * @since 5.4.0   Remove passed vars - rely on widget object in view more template.
 * @since 6.1.4 Changing our nonce verification structures.
 *
 * @version 5.12.0
 *
 * @var array<\WP_Post>      $events                     The array containing the events.
 * @var string               $rest_url                   The REST URL.
 * @var int                  $should_manage_url          int containing if it should manage the URL.
 * @var array<string>        $compatibility_classes      Classes used for the compatibility container.
 * @var array<string>        $container_classes          Classes used for the container of the view.
 * @var array<string,mixed>  $container_data             An additional set of container `data` attributes.
 * @var string               $breakpoint_pointer         String we use as pointer to the current view we are setting up with breakpoints.
 * @var array<string,string> $messages                   An array of user-facing messages, managed by the View.
 * @var boolean              $hide_if_no_upcoming_events Hide widget if no events.
 * @var string               $json_ld_data               The JSON-LD for widget events, if enabled.
 * @var string               $widget_title               The title of the widget.
 */

// Hide widget if no events and widget only displays with events is checked.
if ( empty( $events ) && $hide_if_no_upcoming_events ) {
	return;
}
?>
<div <?php tec_classes( $compatibility_classes ); ?>>
	<div
		<?php tec_classes( $container_classes ); ?>
		data-js="tribe-events-view"
		data-view-rest-url="<?php echo esc_url( $rest_url ); ?>"
		data-view-manage-url="<?php echo esc_attr( $should_manage_url ); ?>"
		<?php foreach ( $container_data as $key => $value ) : ?>
			data-view-<?php echo esc_attr( $key ); ?>="<?php echo esc_attr( $value ); ?>"
		<?php endforeach; ?>
		<?php if ( ! empty( $breakpoint_pointer ) ) : ?>
			data-view-breakpoint-pointer="<?php echo esc_attr( $breakpoint_pointer ); ?>"
		<?php endif; ?>
	>
		<div class="tribe-events-widget-events-list">

			<?php $this->template( 'components/json-ld-data' ); ?>

			<?php $this->template( 'components/data' ); ?>

			<?php if ( ! empty( $widget_title ) ) : ?>
				<header class="tribe-events-widget-events-list__header">
					<h2 class="tribe-events-widget-events-list__header-title tribe-common-h6 tribe-common-h--alt">
						<?php echo esc_html( $widget_title ); ?>
					</h2>
				</header>
			<?php endif; ?>

			<?php if ( ! empty( $events ) ) : ?>

				<div class="tribe-events-widget-events-list__events">
					<?php foreach ( $events as $event ) : ?>
						<?php $this->template( 'widgets/widget-events-list/event', [ 'event' => $event ] ); ?>
					<?php endforeach; ?>
				</div>

				<?php $this->template( 'widgets/widget-events-list/view-more' ); ?>

			<?php else : ?>

				<?php $this->template( 'components/messages' ); ?>

			<?php endif; ?>
		</div>
	</div>
</div>
<?php $this->template( 'components/breakpoints' ); ?>
