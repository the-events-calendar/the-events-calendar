<?php
/**
 * Venue metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( tribe_get_option( 'defaultValueReplace' ) && $post->post_type != TribeEvents::VENUE_POST_TYPE ) {
	$_VenueAddress  = tribe_get_option( 'eventsDefaultAddress' );
	$_VenueCity     = tribe_get_option( 'eventsDefaultCity' );
	$_VenueState    = tribe_get_option( 'eventsDefaultState' );
	$_VenueProvince = tribe_get_option( 'eventsDefaultProvince' );
	$_VenueCountry  = tribe_get_option( 'eventsDefaultCountry' );
	$_VenueZip      = tribe_get_option( 'eventsDefaultZip' );
	$_VenuePhone    = tribe_get_option( 'eventsDefaultPhone' );
}
?>
<?php if ( $post->post_type != TribeEvents::VENUE_POST_TYPE ): ?>
	<tr class="venue">
		<td><?php printf( __( '%s Name:', 'tribe-events-calendar' ), tribe_get_venue_label_singular() ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='venue[Venue]' size='25' value='<?php if ( isset( $_VenueVenue ) ) {
				echo esc_attr( $_VenueVenue );
			} ?>' />
		</td>
	</tr>
<?php endif; ?>
<tr class="venue">
	<td><?php _e( 'Address:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='venue[Address]' size='25' value='<?php if ( isset( $_VenueAddress ) ) {
			echo esc_attr( $_VenueAddress );
		} ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e( 'City:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='venue[City]' size='25' value='<?php if ( isset( $_VenueCity ) ) {
			echo esc_attr( $_VenueCity );
		} ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e( 'Country:', 'tribe-events-calendar' ); ?></td>
	<td>
		<?php
		$countries = TribeEventsViewHelpers::constructCountries( $postId );
		$defaultCountry = tribe_get_option( 'defaultCountry' );
		if ( isset( $_VenueCountry ) && $_VenueCountry ) {
			$current = $_VenueCountry;
		} elseif ( isset( $defaultCountry[1] ) && class_exists( 'TribeEventsPro' ) && tribe_get_option( 'defaultValueReplace' ) ) {
			$current = $defaultCountry[1];
		} else {
			$current = null;
		}
		?>
		<select class="chosen" tabindex="<?php tribe_events_tab_index(); ?>" name='venue[Country]' id="EventCountry">
			<?php
			foreach ( $countries as $abbr => $fullname ) {
				if ( $abbr == '' ) {
					echo '<option value="">' . esc_html( $fullname ) . '</option>';
				} else {
					echo '<option value="' . esc_attr( $fullname ) . '" ';

					selected( ( $current == $fullname ) );

					echo '>' . esc_html( $fullname ) . '</option>';
				}
			}
			?>
		</select>
	</td>
</tr>
<tr class="venue">
	<?php if ( ! isset( $_VenueStateProvince ) || $_VenueStateProvince == "" ) {
		$_VenueStateProvince = - 1;
	}
	$defaultState = tribe_get_option( 'eventsDefaultState' );
	$defaultProvince = tribe_get_option( 'eventsDefaultProvince' );
	$_VenueProvince = isset( $_VenueProvince ) ? $_VenueProvince : '';
	$currentState = ( $_VenueStateProvince == - 1 && tribe_get_option( 'defaultValueReplace' ) ) ? $defaultState : $_VenueStateProvince;
	$currentProvince = ( ! isset( $_VenueProvince ) && tribe_get_option( 'defaultValueReplace' ) ) ? $defaultProvince : $_VenueProvince;
	?>
	<td><?php _e( 'State or Province:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" id="StateProvinceText" name="venue[Province]" type='text' name='' size='25' value='<?php echo ( isset( $_VenueProvince ) && $_VenueProvince != '' ) ? esc_attr( $currentProvince ) : esc_attr( '' ); ?>' />
		<select class="chosen" tabindex="<?php tribe_events_tab_index(); ?>" id="StateProvinceSelect" name="venue[State]">
			<option value=""><?php _e( 'Select a State:', 'tribe-events-calendar' ); ?></option>
			<?php
			foreach ( TribeEventsViewHelpers::loadStates() as $abbr => $fullname ) {
				echo '<option value="' . $abbr . '"';
				selected( ( ( $_VenueStateProvince != - 1 ? $_VenueStateProvince : $currentState ) == $abbr ) );
				echo '>' . esc_html( $fullname ) . '</option>';
			}
			?>
		</select>

	</td>
</tr>
<tr class="venue">
	<td><?php _e( 'Postal Code:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventZip' name='venue[Zip]' size='6' value='<?php if ( isset( $_VenueZip ) ) {
			echo esc_attr( $_VenueZip );
		} ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventPhone' name='venue[Phone]' size='14' value='<?php if ( isset( $_VenuePhone ) ) {
			echo esc_attr( $_VenuePhone );
		} ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e( 'Website:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventWebsite' name='venue[URL]' size='14' value='<?php if ( isset( $_VenueURL ) ) {
			echo esc_attr( $_VenueURL );
		} ?>' /></td>
</tr>

<?php
$google_map_toggle = false;
$google_map_link_toggle = false;

if ( $post->post_type != TribeEvents::VENUE_POST_TYPE ) {

	if ( tribe_get_option( 'embedGoogleMaps', true ) ) { // Only show if embed option selected

		$google_map_toggle = ( tribe_embed_google_map( $postId ) || get_post_status( $postId ) == 'auto-draft' ) ? true : false;
		?>
		<tr id="google_map_toggle">
			<td><?php _e( 'Show Google Map:', 'tribe-events-calendar' ); ?></td>
			<td>
				<input tabindex="<?php tribe_events_tab_index(); ?>" type="checkbox" id="EventShowMap" name="venue[EventShowMap]" value="1" <?php checked( $google_map_toggle ); ?> />
			</td>
		</tr>
	<?php
	}
	$google_map_link_toggle = ( get_post_status( $postId ) == 'auto-draft' && $google_map_toggle ) ? true : get_post_meta( $postId, '_EventShowMapLink', true );
	?>
	<tr id="google_map_link_toggle">
		<td><?php _e( 'Show Google Maps Link:', 'tribe-events-calendar' ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type="checkbox" id="EventShowMapLink" name="venue[EventShowMapLink]" value="1" <?php checked( $google_map_link_toggle ); ?> />
		</td>
	</tr>
<?php
} else {
	if ( tribe_get_option( 'embedGoogleMaps', true ) ) { // Only show if embed option selected

		$google_map_toggle = ( tribe_embed_google_map( $postId ) || get_post_status( $postId ) == 'auto-draft' ) ? true : false;
		?>
		<tr id="google_map_toggle">
			<td><?php _e( 'Show Google Map:', 'tribe-events-calendar' ); ?></td>
			<td>
				<input tabindex="<?php tribe_events_tab_index(); ?>" type="checkbox" id="VenueShowMap" name="venue[ShowMap]" value="true" <?php checked( $google_map_toggle ); ?> />
			</td>
		</tr>
	<?php
	}
	$google_map_link_toggle = ( get_post_status( $postId ) != 'auto-draft' || get_post_meta( $postId, '_VenueShowMapLink', true ) !== 'false' ) ? true : false;
	?>
	<tr id="google_map_link_toggle">
		<td><?php _e( 'Show Google Maps Link:', 'tribe-events-calendar' ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type="checkbox" id="VenueShowMapLink" name="venue[ShowMapLink]" value="true" <?php checked( $google_map_link_toggle ); ?> />
		</td>
	</tr>
<?php
}
?>

<script type="text/javascript">
	jQuery('[name=venue\\[Venue\\]]').blur(function () {
		jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',
			{
				action: 'tribe_event_validation',
				nonce : '<?php echo wp_create_nonce('tribe-validation-nonce'); ?>',
				type  : 'venue',
				name  : jQuery('[name=venue\\[Venue\\]]').get(0).value
			},
			function (result) {
				if (result == 1) {
					jQuery('[name=venue\\[Venue\\]]').parent().removeClass('invalid').addClass('valid');
				} else {
					jQuery('[name=venue\\[Venue\\]]').parent().removeClass('valid').addClass('invalid');
				}
			}
		);
	});
</script>
