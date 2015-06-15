<?php
/**
 * Address Module Template
 * Render an address. This is used by default in the single event view.
 *
 * This view contains the filters required to create an effective address module view.
 *
 * You can recreate an ENTIRELY new address module by doing a template override, and placing
 * a address.php file in a tribe-events/modules/ directory within your theme directory, which
 * will override the /views/modules/address.php.
 *
 * You can use any or all filters included in this file or create your own filters in
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$venue_id = get_the_ID();

$full_region = tribe_get_full_region( $venue_id );

?>
<span class="adr">

<?php
// This location's street address.
if ( tribe_get_address( $venue_id ) ) : ?>
<span class="street-address"><?php echo tribe_get_address( $venue_id ); ?></span>
	<?php if ( ! tribe_is_venue() ) : ?>
		<br>
	<?php endif; ?>
<?php endif; ?>

<?php
// This locations's city.
if ( tribe_get_city( $venue_id ) ) :
	if ( tribe_get_address( $venue_id ) ) : ?>
		<br>
	<?php endif; ?>
	<span class="locality"><?php echo tribe_get_city( $venue_id ); ?></span><span class="delimiter">,</span>
<?php endif; ?>

<?php
// This location's abbreviated region. Full region name in the element title.
if ( tribe_get_region( $venue_id ) ) : ?>
	<abbr class="region tribe-events-abbr" title="<?php esc_attr_e( $full_region ); ?>"><?php echo tribe_get_region( $venue_id ); ?></abbr>
<?php endif; ?>

<?php
// This location's postal code.
if ( tribe_get_zip( $venue_id ) ) : ?>
	<span class="postal-code"><?php echo tribe_get_zip( $venue_id ); ?></span>
<?php endif; ?>

<?php
// This location's country.
if ( tribe_get_country( $venue_id ) ) : ?>
	<span class="country-name"><?php echo tribe_get_country( $venue_id ); ?></span>
<?php endif; ?>

</span>
