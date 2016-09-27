<?php
$indicator_icons = array(
	'good' => 'marker',
	'warning' => 'warning',
	'bad' => 'dismiss',
);
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
		if ( Tribe__Events__Aggregator::instance()->is_service_active() ) {
			$indicator = 'good';
			$text = __( 'Your license is valid', 'the-events-calendar' );
			$ea_active = true;
		} else {
			$service_status = Tribe__Events__Aggregator__Service::instance()->api()->get_error_code();

			$indicator = 'bad';
			if ( 'core:aggregator:invalid-service-key' == $service_status ) {
				$text = __( 'You do not have a license', 'the-events-calendar' );
				$notes = '<a href="https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importsettings&utm_medium=plugin-tec&utm_campaign=in-app">';
				$notes .= esc_html__( 'Buy Event Aggregator to access more event sources and automatic imports!', 'the-events-calendar' );
				$notes .= '</a>';
			} else {
				$text = __( 'Your license is invalid', 'the-events-calendar' );
				$notes = '<a href="' . esc_url( Tribe__Settings::instance()->get_url( array( 'tab' => 'licenses' ) ) ) . '">' . esc_html__( 'Check your license key', 'the-events-calendar' ) . '</a>';
			}
		}
		?>
		<tr>
			<td class="label"><?php esc_html_e( 'License Key', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
		<?php
		// if EA is not active, bail out of the rest of this
		if ( ! $ea_active ) {
			echo '</tbody></table>';
			return ob_get_clean();
		}

		$import_limit = Tribe__Events__Aggregator::instance()->get_daily_limit();
		$import_available = Tribe__Events__Aggregator::instance()->get_daily_limit_available();
		$import_count = $import_limit - $import_available;

		$indicator = 'good';
		$notes = '&nbsp;';

		if ( 0 === $import_limit || $import_count >= $import_limit ) {
			$indicator = 'bad';
			$notes = esc_html__( 'You have reached your daily import limit. Scheduled imports will be paused until tomorrow.', 'the-events-calendar' );
		} elseif ( $import_count / $import_limit >= 0.8 ) {
			$indicator = 'warning';
			$notes = esc_html__( 'You are approaching your daily import limit. You may want to adjust your Scheduled Import frequencies.', 'the-events-calendar' );
		}

		$text = sprintf( // import count and limit
			_n( '%1$d import out of %2$d available today', '%1$d imports out of %2$d available today', $import_count, 'the-events-calendar' ),
			intval( $import_count ),
			intval( $import_limit )
		);

		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Current usage', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
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
		$notes = '&nbsp;';

		$ea_server = Tribe__Events__Aggregator__Service::instance()->api()->domain;
		$up = Tribe__Events__Aggregator__Service::instance()->get( 'status/up' );

		if ( ! $up || is_wp_error( $up ) ) {
			$indicator = 'bad';
			/* translators: %s: Event Aggregator Server URL */
			$text = sprintf( __( 'Not connected to %s', 'the-events-calendar' ), $ea_server );
			$notes = esc_html__( 'The server is not currently responding', 'the-events-calendar' );
		} elseif ( is_object( $up ) && is_object( $up->data ) && isset( $up->data->status ) && 400 <= $up->data->status ) {
			// this is a rare condition that should never happen
			// An example case: the route is not defined on the EA server
			$indicator = 'warning';

			/* translators: %s: Event Aggregator Server URL */
			$text = sprintf( __( 'Not connected to %s', 'the-events-calendar' ), $ea_server );

			$notes = __( 'The server is responding with an error:', 'the-events-calendar' );
			$notes .= '<pre>';
			$notes .= esc_html( $up->message );
			$notes .= '</pre>';
		} else {
			/* translators: %s: Event Aggregator Server URL */
			$text = sprintf( __( 'Connected to %s', 'the-events-calendar' ), $ea_server );
		}

		// @todo - eventually link $text to the status page
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

		// @todo add API request for pingback check
		if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
			$indicator = 'warning';
			$text = __( 'WP Cron not enabled', 'the-events-calendar' );
			$notes = esc_html__( 'Scheduled imports may not run reliably', 'the-events-calendar' );
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

<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Third Party Accounts', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		// Facebook status section
		$indicator = 'good';
		$notes = '&nbsp;';
		$text = 'Connected';

		if ( Tribe__Events__Aggregator::instance()->api( 'origins' )->is_oauth_enabled( 'facebook' ) ) {
			if ( ! Tribe__Events__Aggregator__Settings::instance()->is_fb_credentials_valid() ) {
				$indicator = 'warning';
				$text = __( 'You have not connected Event Aggregator to Facebook', 'the-events-calendar' );
				$facebook_auth_url = Tribe__Events__Aggregator__Record__Facebook::get_auth_url( array( 'back' => 'settings' ) );
				$notes = '<a href="' . esc_url( $facebook_auth_url ). '">' . esc_html_x( 'Connect to Facebook', 'link for connecting facebook', 'the-events-calendar' ) . '</a>';
			}
		} else {
			$indicator = 'warning';
			$text = __( 'Limited connectivity with Facebook', 'the-events-calendar' );
			$notes = esc_html__( 'The service has disabled oAuth. Some types of events may not import.', 'the-events-calendar' );
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo tribe_events_resource_url( 'images/aggregator/facebook.png' ); ?>" /><span><?php esc_html_e( 'Facebook', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
		<?php
		// Meetup status section
		$indicator = 'good';
		$notes = '&nbsp;';
		$text = __( 'API key entered', 'the-events-calendar' );
		$meetup_api_key = tribe_get_option( 'meetup_api_key' );
		if ( ! $meetup_api_key ) {
			$indicator = 'warning';
			$text = __( 'You have not entered a Meetup API key', 'the-events-calendar' );
			$notes = '<a href="' . esc_url( Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) ) ) . '">';
			$notes .= esc_html__( 'Enter your API key', 'the-events-calendar' );
			$notes .= '</a>';
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo tribe_events_resource_url( 'images/aggregator/meetup.png' ); ?>" /><span><?php esc_html_e( 'Meetup', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
	</tbody>
</table>
