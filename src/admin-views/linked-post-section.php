<?php
/**
 * Events post main metabox
 *
 * @version 4.2
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<table id="event_<?php echo esc_attr( $linked_post_type ); ?>" class="linked-post-wrapper eventtable">
	<thead>
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php echo apply_filters( 'tribe_events_linked_post_meta_box_title', $linked_post_type_data['name'], $linked_post_type ); ?></h4>
			</td>
		</tr>
		<?php
		/**
		 * Fires just after the header that appears above the organizer entry form when creating & editing events in the admin
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
		 *
		 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
		 */
		if ( Tribe__Events__Organizer::POSTTYPE === $linked_post_type ) {
			do_action( 'tribe_organizer_table_top', $event->ID );
		} elseif ( Tribe__Events__Venue::POSTTYPE === $linked_post_type ) {
			do_action( 'tribe_location_table_top', $event->ID );
		}
		do_action( 'tribe_linked_post_table_top', $event->ID, $linked_post_type );
		?>
	</thead>
	<?php
	$meta_box = new Tribe__Events__Linked_Posts__Chooser_Meta_Box( $event, $linked_post_type );
	$meta_box->render();
	?>
</table>
<?php
/**
 * Fires after the venue entry form when creating & editing events in the admin
 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
 *
 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
 */
if ( Tribe__Events__Organizer::POSTTYPE === $linked_post_type ) {
	do_action( 'tribe_after_organizer_details', $event->ID );
} elseif ( Tribe__Events__Venue::POSTTYPE === $linked_post_type ) {
	do_action( 'tribe_after_location_details', $event->ID );
}
do_action( 'tribe_after_linked_post_details', $event->ID, $linked_post_type );
