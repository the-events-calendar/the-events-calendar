<?php
global $post;

$post_id = Tribe__Events__Main::postIdHelper();
$is_auto_draft = get_post_status( $post_id ) === 'auto-draft';

// If not $_POST and if this is not an auto-draft then get the current values to edit
if ( ! $_POST && is_admin() ) {

	$venue_name             = tribe_get_venue();

	if ( null === $venue_name ) {

		$defaults = Tribe__Events__Main::instance()->defaults();

		$_VenuePhone            = $defaults->phone();
		$_VenueURL              = strip_tags( $defaults->url() );
		$_VenueAddress          = $defaults->address();
		$_VenueCity             = $defaults->city();
		$_VenueProvince         = $defaults->province();
		$_VenueState            = $defaults->state();
		$_VenueCountry          = $defaults->country();
		$_VenueZip              = $defaults->zip();

	} else {
		$_VenuePhone            = tribe_get_phone();
		$_VenueURL              = strip_tags( tribe_get_venue_website_link( null, null ) );
		$_VenueAddress          = tribe_get_address();
		$_VenueCity             = tribe_get_city();
		$_VenueProvince         = tribe_get_province();
		$_VenueState            = tribe_get_state();
		$_VenueCountry          = tribe_get_country();
		$_VenueZip              = tribe_get_zip();

	}

	$google_map_link_toggle = get_post_meta( $post_id, '_EventShowMapLink', true );
	$google_map_toggle      = tribe_embed_google_map( $post_id );

	//If we just saved use those values from $_POST
} elseif ( ! empty( $_POST ) ) {

	// Special compatibility for array values of these fields, which happens on Community Events submission form.
	if ( isset( $_POST['community-event'] ) && ! empty( $_POST['community-event'] ) ) {

		$venue_name             = isset( $_POST['venue']['Venue'] ) ? esc_attr( $_POST['venue']['Venue'][0] ) : '';
		$_VenuePhone            = isset( $_POST['venue']['Phone'] ) ? esc_attr( $_POST['venue']['Phone'][0] ) : '';
		$_VenueURL              = isset( $_POST['venue']['URL'] ) ? esc_attr( $_POST['venue']['URL'][0] ) : '';
		$_VenueAddress          = isset( $_POST['venue']['Address'] ) ? esc_attr( $_POST['venue']['Address'][0] ) : '';
		$_VenueCity             = isset( $_POST['venue']['City'] ) ? esc_attr( $_POST['venue']['City'][0] ) : '';
		$_VenueProvince         = isset( $_POST['venue']['Province'] ) ? esc_attr( $_POST['venue']['Province'][0] ) : '';
		$_VenueCountry          = isset( $_POST['venue']['Country'] ) ? esc_attr( $_POST['venue']['Country'][0] ) : '';
		$_VenueZip              = isset( $_POST['venue']['Zip'] ) ? esc_attr( $_POST['venue']['Zip'][0] ) : '';

	// "Normal" case, when not on Community Events submission form, in which case these fields are strings.
	} else {

		$venue_name             = isset( $_POST['venue']['Venue'] ) ? esc_attr( $_POST['venue']['Venue'] ) : '';
		$_VenuePhone            = isset( $_POST['venue']['Phone'] ) ? esc_attr( $_POST['venue']['Phone'] ) : '';
		$_VenueURL              = isset( $_POST['venue']['URL'] ) ? esc_attr( $_POST['venue']['URL'] ) : '';
		$_VenueAddress          = isset( $_POST['venue']['Address'] ) ? esc_attr( $_POST['venue']['Address'] ) : '';
		$_VenueCity             = isset( $_POST['venue']['City'] ) ? esc_attr( $_POST['venue']['City'] ) : '';
		$_VenueProvince         = isset( $_POST['venue']['Province'] ) ? esc_attr( $_POST['venue']['Province'] ) : '';
		$_VenueCountry          = isset( $_POST['venue']['Country'] ) ? esc_attr( $_POST['venue']['Country'] ) : '';
		$_VenueZip              = isset( $_POST['venue']['Zip'] ) ? esc_attr( $_POST['venue']['Zip'] ) : '';
	}

	$_VenueState            = isset( $_POST['venue']['State'] ) ? esc_attr( $_POST['venue']['State'] ) : '';
	$google_map_link_toggle = isset( $_POST['EventShowMapLink'] ) ? esc_attr( $_POST['EventShowMapLink'] ) : '';
	$google_map_toggle      = isset( $_POST['EventShowMap'] ) ? esc_attr( $_POST['EventShowMap'] ) : '';
}
?>
<tr class="linked-post venue tribe-linked-type-venue-address">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Address:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			name='venue[Address][]'
			size='25'
			value='<?php echo isset( $_VenueAddress ) ? esc_attr( $_VenueAddress ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue Address', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-city">
	<td class='tribe-table-field-label'><?php esc_html_e( 'City:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			name='venue[City][]'
			size='25'
			value='<?php echo isset( $_VenueCity ) ? esc_attr( $_VenueCity ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue City', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-country">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Country:', 'the-events-calendar' ); ?></td>
	<td>
		<?php
		$countries = Tribe__View_Helpers::constructCountries( $post->ID );

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
			tabindex="<?php tribe_events_tab_index(); ?>"
			name='venue[Country][]'
			id="EventCountry"
			aria-label="<?php esc_html_e( 'Venue Country', 'the-events-calendar' ); ?>"
			data-prevent-clear
		>
			<?php
			foreach ( $countries as $abbr => $fullname ) {
				if ( $abbr == '' ) {
					echo '<option value="">' . esc_html( $fullname ) . '</option>';
				} else {
					echo '<option value="' . esc_attr( $fullname ) . '"' . selected( ( $current == $fullname ), true, false ) . '>' . esc_html( $fullname ) . '</option>';
				}
			}
			?>
		</select>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-state-province">
	<td class='tribe-table-field-label'><?php esc_html_e( 'State or Province:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			id="StateProvinceText"
			name="venue[Province][]"
			type='text'
			name=''
			size='25'
			value='<?php echo isset( $_VenueProvince ) ? esc_attr( $_VenueProvince ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue State', 'the-events-calendar' ); ?>"
		 />
		<select
			class="tribe-dropdown"
			tabindex="<?php tribe_events_tab_index(); ?>"
			id="StateProvinceSelect"
			name="venue[State]"
			aria-label="<?php esc_html_e( 'Venue State', 'the-events-calendar' ); ?>"
			data-prevent-clear
		>
			<option value=""><?php esc_html_e( 'Select a State:', 'the-events-calendar' ); ?></option>
			<?php
			foreach ( Tribe__View_Helpers::loadStates() as $abbr => $fullname ) {
				$selected = selected( ( isset( $_VenueState ) && ( $_VenueState === $abbr || $_VenueState === $fullname ) ), true, false );
				echo '<option value="' . esc_attr( $abbr ) . '" ' . $selected . '>' . esc_html( $fullname ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-zip">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Postal Code:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			id='EventZip'
			name='venue[Zip][]'
			size='6'
			value='<?php echo isset( $_VenueZip ) ? esc_attr( $_VenueZip ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue Zip Code', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-phone">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			id='EventPhone'
			name='venue[Phone][]'
			size='14'
			value='<?php echo isset( $_VenuePhone ) ? esc_attr( $_VenuePhone ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue Phone', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>
<tr class="linked-post venue tribe-linked-type-venue-website">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type='text'
			id='EventWebsite'
			name='venue[URL][]'
			size='14'
			value='<?php echo isset( $_VenueURL ) ? esc_attr( $_VenueURL ) : ''; ?>'
			aria-label="<?php esc_html_e( 'Venue URL', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>

<?php

/**
 * Only show the Google map toggles on the admin screens
 * @since 4.5.4
 *
 */
if ( ! is_admin() ) {
	return;
}

$google_map_toggle = false;
$google_map_link_toggle = false;

if ( empty( $post->post_type ) || $post->post_type != Tribe__Events__Main::VENUE_POST_TYPE ) {
	if ( tribe_get_option( 'embedGoogleMaps', true ) ) { // Only show if embed option selected
		$google_map_toggle = ( tribe_embed_google_map( $post->ID ) || get_post_status( $post->ID ) == 'auto-draft' ) ? true : false;
		?>
		<tr id="google_map_toggle" class="remain-visible tribe-linked-type-venue-googlemap">
			<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map:', 'the-events-calendar' ); ?></td>
			<td>
				<input
					tabindex="<?php tribe_events_tab_index(); ?>"
					type="checkbox"
					id="EventShowMap"
					name="venue[EventShowMap][]"
					value="1"
					<?php checked( $google_map_toggle ); ?>
					aria-label="<?php esc_html_e( 'Show Map?', 'the-events-calendar' ); ?>"
				/>
			</td>
		</tr>
	<?php
	}
	$google_map_link_toggle = ( get_post_status( $post->ID ) == 'auto-draft' && $google_map_toggle ) ? true : get_post_meta( $post->ID, '_EventShowMapLink', true );
	?>
	<tr id="google_map_link_toggle" class="remain-visible tribe-linked-type-venue-googlemap-link">
		<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map Link:', 'the-events-calendar' ); ?></td>
		<td>
			<input
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				id="EventShowMapLink"
				name="venue[EventShowMapLink][]"
				value="1"
				<?php checked( $google_map_link_toggle ); ?>
				aria-label="<?php esc_html_e( 'Show Map Link?', 'the-events-calendar' ); ?>"
			/>
		</td>
	</tr>
	<?php
} else {
	if ( tribe_get_option( 'embedGoogleMaps', true ) ) { // Only show if embed option selected

		$google_map_toggle = ( tribe_embed_google_map( $post->ID ) || get_post_status( $post->ID ) == 'auto-draft' ) ? true : false;
		?>
		<tr id="google_map_toggle" class="remain-visible">
			<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map:', 'the-events-calendar' ); ?></td>
			<td>
				<input
					tabindex="<?php tribe_events_tab_index(); ?>"
					type="checkbox"
					id="VenueShowMap"
					name="venue[ShowMap][]"
					value="true"
					<?php checked( $google_map_toggle ); ?>
				/>
			</td>
		</tr>
	<?php
	}
	$google_map_link_toggle = ( get_post_meta( $post->ID, '_VenueShowMapLink', true ) !== 'false' || get_post_status( $post->ID ) == 'auto-draft' ) ? true : false;
	?>
	<tr id="google_map_link_toggle" class="remain-visible">
		<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map Link:', 'the-events-calendar' ); ?></td>
		<td>
			<input
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				id="VenueShowMapLink"
				name="venue[ShowMapLink][]"
				value="true"
				<?php checked( $google_map_link_toggle ); ?>
			/>
		</td>
	</tr>
	<?php
}
