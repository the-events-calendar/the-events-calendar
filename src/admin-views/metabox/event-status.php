<?php
/**
 * View: Event Status Fields.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/metabox/event-status.php
 *
 * See more documentation about our views templating system.
 *
 * @since   TBD
 *
 * @version TBD
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post $event   The event post object, as decorated by the `tribe_get_event` function.
 * @var Metabox  $metabox The metabox instance.
 */

namespace  Tribe\Extensions\EventsControl;

/**
 * Allow filtering of the event statuses.
 *
 * @since TBD
 *
 * @param array<string|string> An array of video sources.
 * @param \WP_Post $event The current event post object, as decorated by the `tribe_get_event` function.
 */
$statuses = (array) apply_filters( 'tribe_events_event_statuses', [], $event );

?>
<div class="tribe-events-control-metabox-container" style="margin-top: 24px;">
	<?php wp_nonce_field( $metabox::$nonce_action, "{$metabox::$id}[nonce]" ); ?>

	<label for="<?php echo esc_attr( "{$metabox::$id}-status" ); ?>">
		<?php echo esc_html_x( 'Set status:', 'Event status label the select field', 'the-events-calendar' ); ?>
	</label>
	<?php
	$this->template( 'metabox/components/dropdown', [
		'label'    => _x( 'Set status:', 'The label of the event status select.', 'the-events-calendar' ),
		'id'       => "{$metabox::$id}-status",
		'name'     => "{$metabox::$id}[status]",
		'class'    => 'tribe-events-status__status-select',
		'selected' => $event->event_status,
		'attrs'    => [
			'data-placeholder'   => _x( 'Select an Event Status', 'The placeholder for the event status select.', 'the-events-calendar' ),
			'data-hide-search'   => true,
			'data-prevent-clear' => true,
			'data-options'       => json_encode( $statuses ),
			'data-selected'      => $event->event_status,
		]
	] );
	?>
	<div
		class="tribe-dependent"
		data-depends="#<?php echo esc_attr( "{$metabox::$id}-status" ); ?>"
		data-condition='["canceled", "postponed"]'
	>
		<p>
			<label for="<?php echo esc_attr( "{$metabox::$id}-status-reason" ); ?>">
				<?php echo esc_html_x( 'Reason (optional)', 'Label for event status reason field', 'tribe-ext-events-control' ); ?>.
			</label>
			<textarea
				class="components-textarea-control__input"
				id="<?php echo esc_attr( "{$metabox::$id}-status-reason" ); ?>"
				name="<?php echo esc_attr( "{$metabox::$id}[status-reason]" ); ?>"
			><?php echo esc_textarea( $event->event_status_reason ) ?></textarea>
		</p>
	</div>
</div>
