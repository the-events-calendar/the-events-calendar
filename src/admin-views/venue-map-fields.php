<?php
/**
 * Venue map fields.
 *
 * @since 6.2.0
 */

global $post;

/**
 * Only show the Google map toggles on the admin screens
 */
if ( ! is_admin() ) {
	return;
}

$google_map_toggle = ( tribe_embed_google_map( $post->ID ) || get_post_status( $post->ID ) == 'auto-draft' ) ? true : false;
$google_map_link_toggle = ( get_post_status( $post->ID ) == 'auto-draft' && $google_map_toggle ) ? true : get_post_meta( $post->ID, '_EventShowMapLink', true );

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
			<?php esc_html_e( 'Show map link', 'the-events-calendar' ); ?>
		</label>
		<?php
		/**
		 * Fires after the venue map fields have rendered.
		 *
		 * @since 6.2.0
		 */
		do_action( 'tec_events_after_venue_map_fields' );
		?>
	</td>
</tr>
<?php
