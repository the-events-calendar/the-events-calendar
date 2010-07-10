<?php /**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * You can customize this view by putting a replacement file of the same name (events-list-load-widget-display.php) in the events/ directory of your theme.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone, $cost
 * @return string
 */
$EventCity		= get_post_meta( $post->ID, '_EventCity', true );
$EventCountry	= get_post_meta( $post->ID, '_EventCountry', true );
$EventState		= get_post_meta( $post->ID, '_EventState', true );
$EventProvince	= get_post_meta( $post->ID, '_EventProvince', true );
?>

<li class="<?php echo $alt_text ?>">
	<div class="when">
		<?php echo sp_get_start_date( $post->ID, false ); ?>
	</div>
	<div class="event">
		<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="loc"><?php
		$space = false;
		$output = '';
		if ( $city && $EventCity != '' ) {
			$space = true;
			$output = $EventCity . ', ';
		}
		if ( $state || $province ) {
			if ( $EventCountry == "United States" &&  $EventState != '') {
				$space = true;
				$output .= $EventState;
			} elseif  ( $EventProvince != '' ) {
				$space = true;
				$output .= $EventProvince;
			}
		} else {
			$output = rtrim( $output, ', ' );
		}
		$output .= ( $space ) ? '<br />' : '';
		if ( $country && $EventCountry != '') {
			$output .= $EventCountry; 
		}
		echo $output;
	?>
	</div>
</li>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : '';