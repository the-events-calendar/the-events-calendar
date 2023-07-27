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

 // Only show if embed option selected.
if ( tribe_get_option( 'embedGoogleMaps', true ) ) : ?>
	<tr id="google_map_toggle" class="remain-visible tribe-linked-type-venue-googlemap">
		<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map:', 'the-events-calendar' ); ?></td>
		<td>
			<input
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				id="EventShowMap"
				name="EventShowMap"
				value="1"
				<?php checked( $google_map_toggle ); ?>
				aria-label="<?php esc_html_e( 'Show Map?', 'the-events-calendar' ); ?>"
			/>
		</td>
	</tr>
<?php endif; ?>

<tr id="google_map_link_toggle" class="remain-visible tribe-linked-type-venue-googlemap-link">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Show Map Link:', 'the-events-calendar' ); ?></td>
	<td>
		<input
			tabindex="<?php tribe_events_tab_index(); ?>"
			type="checkbox"
			id="EventShowMapLink"
			name="EventShowMapLink"
			value="1"
			<?php checked( $google_map_link_toggle ); ?>
			aria-label="<?php esc_html_e( 'Show Map Link?', 'the-events-calendar' ); ?>"
		/>
	</td>
</tr>
<?php