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
$show_map_link_text = apply_filters( 'tec_events_classic_editor_venue_map_link_text', __( 'Show map link', 'the-events-calendar' ) );

?>
<tr id="google_map_toggle" class="remain-visible tec-linked-post__map-options">
	<td class='tribe-table-field-label'><?php esc_html_e( 'Map:', 'the-events-calendar' ); ?></td>
	<td>
		<?php if ( tribe_get_option( 'embedGoogleMaps', true ) ) : ?>
			<label for="EventShowMap">
				<input
					tabindex="<?php tribe_events_tab_index(); ?>"
					type="checkbox"
					id="EventShowMap"
					name="EventShowMap"
					value="1"
					<?php checked( $google_map_toggle ); ?>
				/>
				<?php esc_html_e( 'Show map', 'the-events-calendar' ); ?>
			</label>
		<?php endif; ?>

		<label for="EventShowMapLink">
			<input
				tabindex="<?php tribe_events_tab_index(); ?>"
				type="checkbox"
				id="EventShowMapLink"
				name="EventShowMapLink"
				value="1"
				<?php checked( $google_map_link_toggle ); ?>
			/>
			<?php echo esc_html( $show_map_link_text ); ?>
		</label>
	</td>
</tr>
<?php
