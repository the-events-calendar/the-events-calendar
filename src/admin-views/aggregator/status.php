<?php
ob_start();

$indicator_icons = array(
	'good' => 'marker',
	'warning' => 'warning',
	'bad' => 'dismiss',
);
?>

<h3><?php esc_html_e( 'Event Aggregator System Status', 'the-events-calendar' ); ?></h3>

<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Your Account Status', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$notes = '&nbsp;';
		$ea_active = false;
		if ( Tribe__Events__Aggregator::instance()->is_service_active() ) {
			$indicator = 'good';
			$text = __( 'Your license is active', 'the-events-calendar' );
			$ea_active = true;
		} else {
			$indicator = 'bad';
			$text = __( 'Your license is inactive', 'the-events-calendar' );
		}
		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Active license', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo esc_html( $notes ); ?></td>
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
			$notes = '&nbsp;';
			$notes = __( 'You have exceeded you daily import limit. Imports will be paused until tomorrow.', 'the-events-calendar' );
		} elseif ( $import_count / $import_limit >= 0.8 ) {
			$indicator = 'warning';
			$notes = __( 'You are approaching your daily import limit, adjust you scheduled imports to avoid problems.', 'the-events-calendar' );
		}

		$text = sprintf( // import count and limit
			_n( '%1$d import today out of %2$d available', '%1$d imports today out of %2$d available', $import_count, 'the-events-calendar' ),
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
			<th colspan="4"><?php esc_html_e( 'Event Aggregator Service Status', 'the-events-calendar' ); ?></th>
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
			$notes = __( 'The Event Aggregator server is not currently responding', 'the-events-calendar' );
		} elseif ( is_object( $up ) && is_object( $up->data ) && isset( $up->data->status ) && 400 >= $up->data->status ) {
			// this is a rare condition that should never happen
			// An example case: the route is not defined on the EA server
			$indicator = 'warning';
			$notes = __( 'The Event Aggregator service is responding with an error:', 'the-events-calendar' );
			$notes .= '<pre>';
			$notes .= esc_html( $up->message );
			$notes .= '</pre>';
		}

		// @todo - eventually this should link to the status page
		$text = $ea_server;
		?>
		<tr>
			<td class="label"><?php esc_html_e( 'Server URL', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
	</tbody>
</table>

<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Your Account Status', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		// Facebook status section
		$indicator = 'good';
		$notes = '&nbsp;';
		$text = '&nbsp;';
		if ( Tribe__Events__Aggregator::instance()->api( 'origins' )->is_oauth_enabled( 'facebook' ) ) {
			if ( ! Tribe__Events__Aggregator__Settings::instance()->is_fb_credentials_valid() ) {
				$indicator = 'warning';
				$text = __( 'You have not connected Event Aggregator to Facebook', 'the-events-calendar' );
				$facebook_auth_url = Tribe__Events__Aggregator__Record__Facebook::get_auth_url( array( 'back' => 'settings' ) );
				$notes = '<a href="' . esc_url( $facebook_auth_url ). '">' . _x( 'Connect to Facebook', 'link for connecting facebook', 'the-events-calendar' ) . '</a>';
			}
		} else {
			$indicator = 'warning';
			$text = __( 'Facebook oAuth is currently disabled', 'the-events-calendar' );
			$notes = __( 'Some types of Facebook events may not be importing correctly (ex: private, group, 18+)', 'the-events-calendar' );
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo tribe_events_resource_url( 'images/aggregator/facebook.png' ); ?>" />
				<span><?php esc_html_e( 'Facebook', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
		<?php
		// Meetup status section
		$indicator = 'good';
		$notes = '&nbsp;';
		$text = '&nbsp;';
		$meetup_api_key = tribe_get_option( 'meetup_api_key' );
		if ( ! $meetup_api_key ) {
			$indicator = 'warning';
			$text = __( 'You have not set your Meetup API key.', 'the-events-calendar' );
			$notes = sprintf( // add link to API tab
				__( 'Add your API key on the %1$sSettings &gt; APIs%2$s page', 'the-events-calendar' ),
				'<a href="' . esc_url( Tribe__Settings::instance()->get_url( array( 'tab' => 'addons' ) ) ) . '">',
				'</a>'
			);
		}
		?>
		<tr>
			<td class="label">
				<img src="<?php echo tribe_events_resource_url( 'images/aggregator/meetup.png' ); ?>" />
				<span><?php esc_html_e( 'Meetup', 'the-events-calendar' ); ?></span>
			</td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
	</tbody>
</table>


<table class="event-aggregator-status">
	<thead>
		<tr class="table-heading">
			<th colspan="4"><?php esc_html_e( 'Import Scheduler Status', 'the-events-calendar' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$indicator = 'good';
		$notes = '&nbsp;';

		// @todo add API request for pingback check
		if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
			$indicator = 'warning';
			$text = __( 'WP Cron is not enabled', 'the-events-calendar' );
			$notes = __( 'Scheduled imports may not run reliably without WP Cron enabled', 'the-events-calendar' );
		} else {
			$text = __( 'WP Cron is enabled', 'the-events-calendar' );
		}

		?>
		<tr>
			<td class="label"><?php esc_html_e( 'WP Cron Status', 'the-events-calendar' ); ?></td>
			<td class="indicator <?php esc_attr_e( $indicator ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $indicator_icons[ $indicator ] ); ?>"></span></td>
			<td><?php echo esc_html( $text ); ?></td>
			<td><?php echo $notes; ?></td>
		</tr>
	</tbody>
</table>

<?php
return ob_get_clean();
