<?php
$indicator_icons = [
	'good'    => 'marker',
	'warning' => 'warning',
	'bad'     => 'dismiss',
];

$show_third_party_accounts = ! is_network_admin();
?>

<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'License &amp; Usage', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$notes = '&nbsp;';
		$ea_active = false;
		if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
			$indicator = 'good';
			$text      = __( 'Your license is valid', 'the-events-calendar' );
			$ea_active = true;
		} else {
			$service_status = tribe( 'events-aggregator.service' )->api()->get_error_code();

			$indicator = 'bad';
			if ( 'core:aggregator:invalid-service-key' == $service_status ) {
				$text   = __( 'You do not have a license', 'the-events-calendar' );
				$notes  = '<a href="https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importsettings&utm_medium=plugin-tec&utm_campaign=in-app">';
				$notes .= esc_html__( 'Buy Event Aggregator to access more event sources and automatic imports!', 'the-events-calendar' );
				$notes .= '</a>';
			} else {
				$text  = __( 'Your license is invalid', 'the-events-calendar' );
				$notes = '<a href="' . esc_url( tribe( 'tec.main' )->settings()->get_url( [ 'tab' => 'licenses' ] ) ) . '">' . esc_html__( 'Check your license key', 'the-events-calendar' ) . '</a>';
			}
		}
		?>
		<tr>
			<td class="label"><?php esc_html_e( 'License Key', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; // Escaping handled above. ?></td>
		</tr>
		<?php
		// if EA is not active, bail out of the rest of this
		if ( ! $ea_active ) {
			echo '</tbody></table>';
			return ob_get_clean();
		}

		$service          = tribe( 'events-aggregator.service' );
		$import_limit     = $service->get_limit( 'import' );
		$import_available = $service->get_limit_remaining();
		$import_count     = $service->get_limit_usage();

		$indicator = 'good';
		$notes     = '&nbsp;';

		if ( 0 === $import_limit || $import_count >= $import_limit ) {
			$indicator = 'bad';
			$notes     = esc_html__( 'You have reached your daily import limit. Scheduled imports will be paused until tomorrow.', 'the-events-calendar' );
		} elseif ( $import_count / $import_limit >= 0.8 ) {
			$indicator = 'warning';
			$notes     = esc_html__( 'You are approaching your daily import limit. You may want to adjust your Scheduled Import frequencies.', 'the-events-calendar' );
		}

		$text = sprintf( // import count and limit
			_n( '%1$d import used out of %2$d available today', '%1$d imports used out of %2$d available today', $import_count, 'the-events-calendar' ),
			intval( $import_count ),
			intval( $import_limit )
		);

		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Current usage', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes;  // Escaping handled above. ?></td>
		</tr>
	</tbody>
</table>

<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Import Services', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$indicator = 'good';
		$notes     = '&nbsp;';

		$ea_server = tribe( 'events-aggregator.service' )->api()->domain;
		$up        = tribe( 'events-aggregator.service' )->get( 'status/up' );

		if ( ! $up || is_wp_error( $up ) ) {
			$indicator = 'bad';
			/* translators: %s: Event Aggregator Server URL */
			$text  = sprintf( __( 'Not connected to %s', 'the-events-calendar' ), $ea_server );
			$notes = esc_html__( 'The server is not currently responding', 'the-events-calendar' );
		} elseif ( is_object( $up ) && is_object( $up->data ) && isset( $up->data->status ) && 400 <= $up->data->status ) {
			// this is a rare condition that should never happen
			// An example case: the route is not defined on the EA server
			$indicator = 'warning';

			/* translators: %s: Event Aggregator Server URL */
			$text = sprintf( __( 'Not connected to %s', 'the-events-calendar' ), $ea_server );

			$notes  = __( 'The server is responding with an error:', 'the-events-calendar' );
			$notes .= '<pre>';
			$notes .= esc_html( $up->message );
			$notes .= '</pre>';
		} else {
			/* translators: %s: Event Aggregator Server URL */
			$text = sprintf( __( 'Connected to %s', 'the-events-calendar' ), $ea_server );
		}

		// @todo [BTRIA-611]: Link $text to the status page.
		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Server Connection', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
		<?php
		$indicator = 'good';
		$notes = '&nbsp;';

		// @todo [BTRIA-612]: add API request for pingback check
		if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
			$indicator = 'warning';
			$text      = __( 'WP Cron not enabled', 'the-events-calendar' );
			$notes     = esc_html__( 'Scheduled imports may not run reliably', 'the-events-calendar' );
		} else {
			$text = __( 'WP Cron enabled', 'the-events-calendar' );
		}

		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Scheduler Status', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
	</tbody>
</table>

<?php if ( $show_third_party_accounts ) : ?>
	<table class="event-aggregator-status">
		<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Third Party Accounts', 'the-events-calendar' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		// Eventbrite status section
		$indicator = 'good';
		$notes     = '&nbsp;';
		$text      = 'Connected';

		if ( tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'eventbrite' ) ) {
			if ( ! tribe( 'events-aggregator.settings' )->has_eb_security_key() ) {
				$indicator = 'warning';
				$text = __( 'You have not connected Event Aggregator to Eventbrite', 'the-events-calendar' );
				$eventbrite_auth_url = Tribe__Events__Aggregator__Record__Eventbrite::get_auth_url(
						[ 'back' => 'settings' ]
				);
				$notes = '<a href="' . esc_url( $eventbrite_auth_url ). '">' . esc_html_x( 'Connect to Eventbrite', 'link for connecting eventbrite', 'the-events-calendar' ) . '</a>';
			}
		} else {
			$indicator = 'warning';
			$text = __( 'Limited connectivity with Eventbrite', 'the-events-calendar' );
			$notes = esc_html__( 'The service has disabled oAuth. Some types of events may not import.', 'the-events-calendar' );
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo esc_url( tribe_events_resource_url( 'images/aggregator/eventbrite.png' ) ); ?>" /><span><?php esc_html_e( 'Eventbrite', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; // Escaping handled above. ?></td>
		</tr>
		<?php
		// Meetup status section
		$indicator = 'good';
		$notes     = '&nbsp;';
		$text      = 'Connected';

		if ( tribe( 'events-aggregator.main' )->api( 'origins' )->is_oauth_enabled( 'meetup' ) ) {
			if ( ! tribe( 'events-aggregator.settings' )->has_meetup_security_key() ) {
				$indicator = 'warning';
				$text = __( 'You have not connected Event Aggregator to Meetup', 'the-events-calendar' );
				$meetup_auth_url = Tribe__Events__Aggregator__Record__Meetup::get_auth_url( [ 'back' => 'settings' ] );
				$notes = '<a href="' . esc_url( $meetup_auth_url ). '">' . esc_html_x( 'Connect to Meetup', 'link for connecting meetup', 'the-events-calendar' ) . '</a>';
			}
		} else {
			$indicator = 'warning';
			$text = __( 'Limited connectivity with Meetup', 'the-events-calendar' );
			$notes = esc_html__( 'The service has disabled oAuth. Some types of events may not import.', 'the-events-calendar' );
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo esc_url( tribe_events_resource_url( 'images/aggregator/meetup.png' ) ); ?>" /><span><?php esc_html_e( 'Meetup', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; // Escaping handled above. ?></td>
		</tr>
		<?php
		/**
		 * Fires below the rows in the third party status table.
		 *
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s).
		 *
		 * @since 4.6.24
		 *
		 * @param array $indicator_icons List of indicator icons.
		 */
		do_action( 'tribe_events_status_third_party', $indicator_icons );
		?>
		</tbody>
	</table>
<?php endif; ?>
