<?php
/**
 * This is the template for the output of the events list widget.
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * You can customize this view by putting a replacement file of the same name (events-list-load-widget-display.php) in the events/ directory of your theme.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone, $cost
 * @return string
 */

// Vars set:
// '$event->AllDay',
// '$event->StartDate',
// '$event->EndDate',
// '$event->ShowMapLink',
// '$event->ShowMap',
// '$event->Cost',
// '$event->Phone',

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$event = array();
$tribe_ecp = TribeEvents::instance();
reset($tribe_ecp->metaTags); // Move pointer to beginning of array.
foreach($tribe_ecp->metaTags as $tag){
	$var_name = str_replace('_Event','',$tag);
	$event[$var_name] = tribe_get_event_meta( $post->ID, $tag, true );
}

$event = (object) $event; //Easier to work with.

ob_start();
if ( !isset($alt_text) ) { $alt_text = ''; }
post_class($alt_text,$post->ID);
$class = ob_get_contents();
ob_end_clean();
?>
<li <?php echo $class ?>>
	<div class="when">
		<?php
			$space = false;
			$output = '';
			echo tribe_get_start_date( $post->ID );

			if( $end && $event->EndDate != '' ) {
				if ( ( tribe_get_all_day( $post->ID ) != 'yes' && tribe_is_multiday( $post->ID ) ) || ( tribe_get_all_day( $post->ID ) == 'yes' && tribe_is_multiday( $post->ID ) ) ) {
					echo ' – <br/>'. tribe_get_end_date($post->ID);
				} elseif ( tribe_get_all_day( $post->ID ) != 'yes' && !tribe_is_multiday( $post->ID ) ) {
					echo ' – <br/>'. tribe_get_end_date($post->ID, false, 'g:i a');
				}
			}

			if( $event->AllDay ) {
				echo ' <small><em>('.__( 'All Day', 'tribe-events-calendar-pro' ).')</em></small>';
         	}
		?>
	</div>
	<div class="event">
		<a href="<?php echo tribe_get_event_link($post) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="loc"><?php
		if ( $venue && tribe_get_venue() != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= tribe_get_venue();
			$space = true;
		}

		if ( $address && tribe_get_address()) {
			$output .= ( $space ) ? '<br />' : '';
			$output .= tribe_get_address();
			$space = true;
		}

		if ( $city && tribe_get_city() != '' ) {
			$output .= ( $space ) ? '<br />' : '';
			$output .= tribe_get_city() . ', ';
			$space = true;
		}
		if ( $region && tribe_get_region()) {
			$output .= ( !$city ) ? '<br />' : '';
			$space = true;
			$output .= tribe_get_region();
		} else {
			$output = rtrim( $output, ', ' );
		}

		if ( $zip && tribe_get_zip() != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= tribe_get_zip();
			$space = true;
		}

		if ( $country && tribe_get_country() != '') {
			$output .= ( $space ) ? '<br />' : ' ';
			$output .= tribe_get_country();
		}

		if ( $phone && tribe_get_phone() != '') {
			if($output)
				$output .= '<br/>';

			$output .= tribe_get_phone();
		}
		if ( $cost && tribe_get_cost() != '') {
			if($output)
				$output .= '<br/>';
			$output .= __('Price:', 'tribe-events-calendar-pro') . ' ' . tribe_get_cost();
		}

		echo $output;
	?>
	</div>
</li>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : ''; ?>
