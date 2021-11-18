<?php
/**
 * Events Control mark as online replacement.
 *
 * This metabox template will replace the one used by the Events Control extension when both the extension and
 * this plugin are active to ensure the online/virtual event status is managed by this plugin.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/admin-views/metabox/compatibility/events-control-extension/metabox-container.php
 *
 * See more documentation about our views templating system.
 *
 * @since   5.11.0
 *
 * @version 5.11.0
 *
 * @link    http://evnt.is/1aiy
 *
 * @var \WP_Post                               $event      The event post object, as decorated by the `tribe_get_event` function.
 * @var array<string|mixed>                    $fields     Array of field values for marked online.
 * @var Tribe\Extensions\EventsControl\Metabox $metabox_id The metabox instance, as passed by the extension..
 */

?>
<div class="tribe-events-control-metabox-container" style="margin-top: 18px;">
	<?php wp_nonce_field( $metabox::$nonce_action, "{$metabox::$id}[nonce]" ); ?>
	<div>
		<p>
			<label for="<?php echo esc_attr( "{$metabox::$id}-online" ); ?>">
				<input
					id="<?php echo esc_attr( "{$metabox::$id}-online" ); ?>"
					name="<?php echo esc_attr( "{$metabox::$id}[online]" ); ?>"
					type="checkbox"
					value="yes"
					<?php checked( $fields['online'] ); ?>
				>
				<?php echo esc_html_x( 'Mark as an online event', 'Event State of being Online only checkbox label', 'the-events-calendar' ); ?>
			</label>
		</p>
		<div
			class="tribe-dependent"
			data-depends="#<?php echo esc_attr( "{$metabox::$id}-online" ); ?>"
			data-condition-checked
		>
			<p>
				<label
					class="tribe-events-status-components-online-url__label"
					for="<?php echo esc_attr( "{$metabox::$id}-online-url" ); ?>"
				>
					<?php echo esc_html_x( 'Live Stream URL', 'Label for live stream URL field', 'the-events-calendar' ); ?>
				</label>
				<input
					id="<?php echo esc_attr( "{$metabox::$id}-online-url" ); ?>"
					name="<?php echo esc_attr( "{$metabox::$id}[online-url]" ); ?>"
					value="<?php echo esc_url( $fields['online-url'] ) ?>"
					type="url"
					class="components-text-control__input"
				>
			</p>
		</div>
	</div>
</div>
