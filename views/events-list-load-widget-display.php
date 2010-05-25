<?php /**
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 * @return string
 */
$EventCity		= get_post_meta( $post->ID, '_EventCity', true );
$EventCountry	= get_post_meta( $post->ID, '_EventCountry', true );
$EventState		= get_post_meta( $post->ID, '_EventState', true );
$EventProvince	= get_post_meta( $post->ID, '_EventProvince', true );
?>

<li class="<?php echo $alt_text ?>">
	<div class="when">
		<?php echo the_event_start_date( $post->ID, false ); ?>
	</div>
	<div class="event">
		<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="loc"><?php
		$space = false;
		$output = '';
		if ($city == true && $EventCity != '') {
			$space = true;
			$output = $EventCity . ', ';
		}
		if ($state == true || $province == true){
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
		if ( $space ) {
			$output .=  '<br />';
		}
		if ($country == true && $EventCountry != '') {
			$output .= $EventCountry; 
		}
		echo $output;
	?>
	</div>
	<a class="more-link" href="<?php echo get_permalink($post->ID) ?>"><?php _e('More Info', $this->pluginDomain); ?></a>
</li>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : '';