<?php
/**
 * Venue map fields.
 */

global $post;

/**
 * Only show the Google map toggles on the admin screens
 * @since 4.5.4
 *
 */
if ( ! is_admin() ) {
	return;
}

$google_map_toggle = ( tribe_embed_google_map( $post->ID ) || get_post_status( $post->ID ) == 'auto-draft' ) ? true : false;
$google_map_link_toggle = ( get_post_status( $post->ID ) == 'auto-draft' && $google_map_toggle ) ? true : get_post_meta( $post->ID, '_EventShowMapLink', true );

/**
 * Allows for the filtering of the checkbox label
 */
$show_map_link_text = apply_filters( 'tec_events_classic_editor_venue_map_link_text', __( 'Show map link:', 'the-events-calendar' ) );
$show_map_link_aria_text = apply_filters( 'tec_events_classic_editor_venue_map_link_aria_text', __( 'Show map link?', 'the-events-calendar' ) );

?>
<tfoot>
<?php

 // Only show if embed option selected.
if ( tribe_get_option( 'embedGoogleMaps', true ) ) : ?>
	<tr id="google_map_toggle" class="remain-visible tribe-linked-type-venue-googlemap">
		<td class='tribe-table-field-label'><?php esc_html_e( 'Show map:', 'the-events-calendar' ); ?></td>
		<td>
			<input
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				id="EventShowMap"
				name="EventShowMap"
				value="1"
				<?php checked( $google_map_toggle ); ?>
				aria-label="<?php esc_html_e( 'Show map?', 'the-events-calendar' ); ?>"
			/>
		</td>
	</tr>
<?php endif; ?>

<tr id="google_map_link_toggle" class="remain-visible tribe-linked-type-venue-googlemap-link">
	<td class='tribe-table-field-label'><?php echo esc_html( $show_map_link_text ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type="checkbox"
			id="EventShowMapLink"
			name="EventShowMapLink"
			value="1"
			<?php checked( $google_map_link_toggle ); ?>
			aria-label="<?php echo esc_html( $show_map_link_aria_text ); ?>"
		/>
	</td>
</tr>
</tfoot>
<?php