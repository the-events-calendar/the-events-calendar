<?php
/**
 * UI for option to hide from upcoming events list
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<?php global $post; ?>
<label class="selectit"><input value="yes" type="checkbox" <?php checked( tribe_get_event_meta( $post->ID, '_EventHideFromUpcoming' ) == 'yes' ) ?> name="EventHideFromUpcoming"> <?php printf( esc_html__( 'Hide From %s Listings', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?>
</label><br /><br />
<label class="selectit"><input value="yes" type="checkbox" <?php checked( $post->menu_order == '-1' ) ?> name="EventShowInCalendar"> <?php esc_html_e( 'Sticky in Month View', 'the-events-calendar' ); ?>
</label> <span class="dashicons dashicons-editor-help tribe-sticky-tooltip" title="<?php esc_attr_e( "When events are sticky in month view, they'll display first in the list of events shown within a given day block.", 'the-events-calendar' ); ?>"></span>
