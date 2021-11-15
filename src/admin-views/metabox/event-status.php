<?php
/**
 * View: Event Status Fields.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/metabox/event-status.php
 *
 * See more documentation about our views templating system.
 *
 * @since   5.11.0
 *
 * @version 5.11.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post $event   The event post object, as decorated by the `tribe_get_event` function.
 * @var Metabox  $metabox The metabox instance.
 */

$current_status = empty( $event->event_status ) ? '' : $event->event_status;
/**
 * Allow filtering of the event statuses.
 *
 * @since 5.11.0
 *
 * @param array<string|string> 					An array of video sources.
 * @param string 				$current_status The current event status for the event or empty string if none.
 */
$statuses = (array) apply_filters( 'tec_event_statuses', [], $current_status );

?>
<div class="tribe-events-status_metabox__container">
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
		'options'  => $statuses,
		'selected' => $current_status,
		'attrs'    => [
			'data-placeholder'   => _x( 'Select an Event Status', 'The placeholder for the event status select.', 'the-events-calendar' ),
			'data-hide-search'   => true,
			'data-prevent-clear' => true,
			'data-options'       => json_encode( $statuses ),
			'data-selected'      => $current_status,
		]
	] );
	?>
	<div
		class="tribe-dependent"
		data-depends="#<?php echo esc_attr( "{$metabox::$id}-status" ); ?>"
		data-condition='["canceled", "postponed"]'
	>
		<div class="tribe-events-status-components-textarea-control__container">
			<label
				class="tribe-events-status-components-textarea-control__label"
				for="<?php echo esc_attr( "{$metabox::$id}-status-reason" ); ?>"
			>
				<?php echo esc_html_x( 'Reason (optional)', 'Label for event status reason field', 'the-events-calendar' ); ?>.
			</label>
			<textarea
				class="tribe-events-status-components-textarea-control__input"
				id="<?php echo esc_attr( "{$metabox::$id}-status-reason" ); ?>"
				name="<?php echo esc_attr( "{$metabox::$id}[status-reason]" ); ?>"
			><?php echo esc_textarea( $event->event_status_reason ) ?></textarea>
		</div>
	</div>
</div>
