<?php
/**
 * Events post main metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular           = tribe_get_event_label_singular();
$events_label_plural             = tribe_get_event_label_plural();
$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();
$events_label_plural_lowercase   = tribe_get_event_label_plural_lowercase();
?>
<div id="eventIntro">
	<div id="tribe-events-post-error" class="tribe-events-error error"></div>
	<?php
	/**
	 * Fires inside the top of "The Events Calendar" meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_post_errors', $event->ID, true );
	?>
</div>
<div id='eventDetails' class="inside eventForm" data-datepicker_format="<?php echo esc_attr( tribe_get_option( 'datepickerFormat' ) ); ?>">
	<?php
	/**
	 * Fires inside the opening #eventDetails div of The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_detail_top', $event->ID, true );

	wp_nonce_field( Tribe__Events__Main::POSTTYPE, 'ecp_nonce' );

	/**
	 * Fires after the nonce field inside The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_eventform_top', $event->ID );
	?>
	
	<?php Tribe__Events__Linked_Posts::instance()->render_meta_box_sections( $event ); ?>

	<table id="event_url" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php printf( esc_html__( '%s Website', 'the-events-calendar' ), $events_label_singular ); ?></h4></td>
		</tr>
		<tr>
			<td style="width:172px;"><?php esc_html_e( 'URL:', 'the-events-calendar' ); ?></td>
			<td>
				<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventURL' name='EventURL' size='25' value='<?php echo ( isset( $_EventURL ) ) ? esc_attr( $_EventURL ) : ''; ?>' placeholder='example.com' />
			</td>
		</tr>
		<?php
		/**
		 * Fires just after the "URL" field that appears below the Event Website header in The Events Calendar meta box
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
		 *
		 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
		 * @param boolean
		 */
		do_action( 'tribe_events_url_table', $event->ID, true );
		?>
	</table>

	<?php
	/**
	 * Fires just after closing table tag after Event Website in The Events Calendar meta box
	 *
	 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
	 * @param boolean
	 */
	do_action( 'tribe_events_details_table_bottom', $event->ID, true );
	?>

	<table id="event_cost" class="eventtable">
		<?php if ( tribe_events_admin_show_cost_field() ) : ?>
			<tr>
				<td colspan="2" class="tribe_sectionheader">
					<h4><?php printf( esc_html__( '%s Cost', 'the-events-calendar' ), $events_label_singular ); ?></h4></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Currency Symbol:', 'the-events-calendar' ); ?></td>
				<td>
					<input
						tabindex="<?php tribe_events_tab_index(); ?>"
						type='text'
						id='EventCurrencySymbol'
						name='EventCurrencySymbol'
						size='2'
						value='<?php echo isset( $_EventCurrencySymbol ) ? esc_attr( $_EventCurrencySymbol ) : tribe_get_option( 'defaultCurrencySymbol', '$' ); ?>'
						class='alignleft'
					/>
					<select
						tabindex="<?php tribe_events_tab_index(); ?>"
						id="EventCurrencyPosition"
						name="EventCurrencyPosition"
						class="tribe-dropdown"
					>
						<?php
						if ( isset( $_EventCurrencyPosition ) && 'suffix' === $_EventCurrencyPosition ) {
							$suffix = true;
						} elseif ( isset( $_EventCurrencyPosition ) && 'prefix' === $_EventCurrencyPosition ) {
							$suffix = false;
						} elseif ( true === tribe_get_option( 'reverseCurrencyPosition', false ) ) {
							$suffix = true;
						} else {
							$suffix = false;
						}
						?>
						<option value="prefix"> <?php _ex( 'Before cost', 'Currency symbol position', 'the-events-calendar' ) ?> </option>
						<option value="suffix"<?php if ( $suffix ) {
							echo ' selected="selected"';
						} ?>><?php _ex( 'After cost', 'Currency symbol position', 'the-events-calendar' ) ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Cost:', 'the-events-calendar' ); ?></td>
				<td>
					<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo ( isset( $_EventCost ) ) ? esc_attr( $_EventCost ) : ''; ?>' />
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<small><?php printf( esc_html__( 'Enter a 0 for %s that are free or leave blank to hide the field.', 'the-events-calendar' ), $events_label_plural_lowercase ); ?></small>
				</td>
			</tr>
		<?php endif; ?>
		<?php
		/**
		 * Fires just after the "Cost" field that appears below the Event Cost header in The Events Calendar meta box
		 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
		 *
		 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
		 * @param boolean
		 */
		do_action( 'tribe_events_cost_table', $event->ID, true );
		?>
	</table>

</div>
<?php
/**
 * Fires at the bottom of The Events Calendar meta box
 *
 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
 * @param boolean
 */
do_action( 'tribe_events_above_donate', $event->ID, true );

/**
 * Fires at the bottom of The Events Calendar meta box
 *
 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
 * @param boolean
 */
do_action( 'tribe_events_details_bottom', $event->ID, true );
