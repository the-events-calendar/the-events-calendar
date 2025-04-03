<?php
/**
 * Venue metabox
 *
 * @var $_VenueAddress
 * @var $_VenueCity
 * @var $_VenueState
 * @var $_VenueProvince
 * @var $_VenueCountry
 * @var $_VenueZip
 * @var $_VenuePhone
 */

global $post;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$google_map_toggle      = ( tribe_embed_google_map( $event->ID ) || get_post_status( $event->ID ) == 'auto-draft' ) ? true : false;
$google_map_link_toggle = ( get_post_status( $event->ID ) == 'auto-draft' || get_post_meta( $event->ID, '_VenueShowMapLink', true ) !== 'false' ) ? true : false;

/**
 * Action to insert content before the venue metabox.
 *
 * @since 4.2
 *
 * @param WP_Post $post The global $post we're editing (could be venue or event).
 */
do_action( 'tribe_events_venue_before_metabox', $post );
?>
<tr class="venue tribe-linked-type-venue-address">
	<td class='tribe-table-field-label'>
		<label for="venueAddress">
			<?php esc_html_e( 'Address:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id="venueAddress"
			name="venue[Address]"
			size="25"
			tabindex="<?php tribe_events_tab_index(); ?>"
			type="text"
			value='<?php echo ( isset( $_VenueAddress ) ? esc_attr( $_VenueAddress ) : '' ); ?>'
		/>
	</td>
</tr>
<tr class="venue tribe-linked-type-venue-city">
	<td class='tribe-table-field-label'>
		<label for="venueCity">
			<?php esc_html_e( 'City:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id="venueCity"
			name='venue[City]'
			size='25'
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			value='<?php echo ( isset( $_VenueCity ) ? esc_attr( $_VenueCity ) : '' ); ?>'
		/>
	</td>
</tr>
<tr class="venue tribe-linked-type-venue-country">
	<td class='tribe-table-field-label'>
		<label for="EventCountry">
			<?php esc_html_e( 'Country:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<?php
		$countries = Tribe__View_Helpers::constructCountries( $event->ID );

		if ( isset( $_VenueCountry ) && $_VenueCountry ) {
			$current = $_VenueCountry;
		} else {
			$current = null;
		}

		if ( is_array( $current ) && isset( $current[1] ) ) {
			$current = $current[1];
		}
		?>
		<select
			class="tribe-dropdown"
			id="EventCountry"
			name="venue[Country]"
			tabindex="<?php tribe_events_tab_index(); ?>"
		>
			<?php
			foreach ( $countries as $abbr => $fullname ) {
				if ( $abbr == '' ) {
					echo '<option value="">' . esc_html( $fullname ) . '</option>';
				} else {
					echo '<option value="' . esc_attr( $fullname ) . '" ' . selected( ( $current == $fullname ), true, false ) . '>' . esc_html( $fullname ) . '</option>';
				}
			}
			?>
		</select>
	</td>
</tr>
<tr class="venue tribe-linked-type-venue-state-province">
	<?php
	if ( 'auto-draft' === get_post_status() && empty( $_VenueStateProvince ) ) {
		$currentState    = tribe_get_default_value( 'state' );
		$currentProvince = tribe_get_default_value( 'province' );
	} else {
		$currentProvince = $_VenueProvince;
		$currentState    = $_VenueState;
	}
	?>
	<td class='tribe-table-field-label'>
		<label for="StateProvinceSelect">
			<?php esc_html_e( 'State or Province:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id="StateProvinceText"
			name="venue[Province]"
			size='25'
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			value='<?php echo esc_attr( $currentProvince ); ?>'
		/>
		<select
			class="tribe-dropdown"
			id="StateProvinceSelect"
			name="venue[State]"
			tabindex="<?php tribe_events_tab_index(); ?>"
		>
			<option value=""><?php esc_html_e( 'Select a State:', 'the-events-calendar' ); ?></option>
			<?php
			foreach ( Tribe__View_Helpers::loadStates() as $abbr => $fullname ) {
				// support matching by state abbreviation OR fullname.
				// NOTE: converts to abbreviation on save
				$selected = selected( ( $currentState === $abbr || $currentState === $fullname ), true, false );
				echo '<option value="' . esc_attr( $abbr ) . '" ' . $selected . '>' . esc_html( $fullname ) . '</option>';
			}
			?>
		</select>

	</td>
</tr>
<tr class="venue tribe-linked-type-venue-zip">
	<td class='tribe-table-field-label'>
		<label for="EventZip">
			<?php esc_html_e( 'Postal Code:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id='EventZip'
			name='venue[Zip]'
			size='6'
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			value='<?php echo ( isset( $_VenueZip ) ? esc_attr( $_VenueZip ) : '' ); ?>'
		/>
	</td>
</tr>
<tr class="venue tribe-linked-type-venue-phone">
	<td class='tribe-table-field-label'>
		<label for="EventPhone">
			<?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id='EventPhone'
			name='venue[Phone]'
			size='14'
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			value='<?php echo ( isset( $_VenuePhone ) ? esc_attr( $_VenuePhone ) : '' ); ?>'
		/>
	</td>
</tr>
<tr class="venue tribe-linked-type-venue-website">
	<td class='tribe-table-field-label'>
		<label for="EventWebsite">
			<?php esc_html_e( 'Website:', 'the-events-calendar' ); ?>
		</label>
	</td>
	<td>
		<input
			id='EventWebsite'
			name='venue[URL]'
			size='14'
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='url'
			value='<?php echo ( isset( $_VenueURL ) ? esc_attr( $_VenueURL ) : '' ); ?>'
		/>
	</td>
</tr>

<?php
    // Only show if embed option selected
	if ( tribe_get_option( 'embedGoogleMaps', true ) ) :
		?>
		<tr id="google_map_toggle" class="remain-visible">
			<td class='tribe-table-field-label'>
				<label for="VenueShowMap">
					<?php esc_html_e( 'Show Map:', 'the-events-calendar' ); ?>
				</label>
			</td>
			<td>
				<input
					tabindex="<?php tribe_events_tab_index(); ?>"
					type="checkbox"
					id="VenueShowMap"
					name="venue[ShowMap]"
					value="true"
					<?php checked( $google_map_toggle ); ?>
				/>
			</td>
		</tr>
	<?php endif; ?>
	<tr id="google_map_link_toggle" class="remain-visible">
		<td class='tribe-table-field-label'>
			<label for="VenueShowMapLink' ); ?>">
				<?php esc_html_e( 'Show Map Link:', 'the-events-calendar' ); ?>
			</label>
		</td>
		<td>
			<input
				id="VenueShowMapLink"
				name="venue[ShowMapLink]"
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				value="true"
				<?php checked( $google_map_link_toggle ); ?>
			/>
		</td>
	</tr>
<?php
/**
 * Action to insert content after the venue metabox.
 *
 * @since 4.2
 *
 * @param WP_Post $post The global $post we're editing (could be venue or event).
 */
do_action( 'tribe_events_after_venue_metabox', $post );
?>

<script>
	jQuery( '#venueName' ).on( 'blur', function () {
		jQuery.post('<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>',
			{
				action: 'tribe_event_validation',
				nonce : <?php echo json_encode( wp_create_nonce( 'tribe-validation-nonce' ) ); ?>,
				type  : 'venue',
				name  : jQuery('#venueName').get(0).value
			},
			function (result) {
				jQuery('.tribe-venue-error').remove();

				if ( jQuery( '#venueName' ).get(0).value == "" ) {
					jQuery( '#venueName' ).after(
						'<div class="tribe-venue-error error form-invalid"><?php
							printf( esc_html__( '%s Name can not be empty', 'the-events-calendar' ), tribe_get_venue_label_singular() );
						?></div>'
					);
				} else if ( result != 1 ) {
					jQuery( '#venueName' ).after(
						'<div class="tribe-venue-error error form-invalid"><?php
							printf( esc_html__( '%s Name already exists', 'the-events-calendar' ), tribe_get_venue_label_singular() );
						?></div>'
					);
				}
			}
		);
	});
</script>
