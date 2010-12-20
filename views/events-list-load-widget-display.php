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

//Vars set:
// 			'$event->AllDay',
// 			'$event->StartDate',
// 			'$event->EndDate',
// 			'$event->ShowMapLink',
// 			'$event->ShowMap',
// 			'$event->Cost',
// 			'$event->Phone',

	$event = array();
	global $sp_ecp;
	reset($sp_ecp->metaTags); // Move pointer to beginning of array.
	foreach($sp_ecp->metaTags as $tag){
		$var_name = str_replace('_Event','',$tag);
		$event[$var_name] = getEventMeta( $post->ID, $tag, true );
	}

	$event = (object) $event; //Easier to work with.

	ob_start();
		post_class($alt_text,$post->ID);
	$class = ob_get_contents();
	ob_end_clean();
?>
<li <?php echo $class ?>>
	<div class="when">
		<?php 
			echo sp_get_start_date( $post->ID, $start ); 

			if($event->AllDay && $start)
				echo ' <small>('.__('All Day',$this->pluginDomain).')</small>';
		?> 
	</div>
	<div class="event">
		<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="loc"><?php
		$space = false;
		$output = '';

		if ( $venue && sp_get_venue() != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= sp_get_venue(); 
			$space = true;
		}

		if ( $address && sp_get_address()) {
			$output .= ( $space ) ? '<br />' : '';
			$output .= sp_get_address(); 
		}

		if ( $city && sp_get_city() != '' ) {
			$space = true;
			$output .= sp_get_city() . ', ';
		}
		if ( $region && sp_get_region()) {
			$output .= ( !$city ) ? '<br />' : '';
			$space = true;
			$output .= sp_get_region();
		} else {
			$output = rtrim( $output, ', ' );
		}

		if ( $zip && sp_get_zip() != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= sp_get_zip();
			$space = true;
		}

		if ( $country && sp_get_country() != '') {
			$output .= ( $space ) ? '<br />' : ' ';
			$output .= sp_get_country(); 
		}


// 		if ( $start && $event->StartDate != '') {
// 			$output .= '<br/>';
// 			if($end)
// 				$output .= __('From ', $this->pluginDomain);
// 			$output .= sp_get_start_date($post->ID); 
// 
// 			if($end)
// 				$output .= __(' until ', $this->pluginDomain);
// 		}

		if ( $end && $event->EndDate != '') {
			if($output) //It is entirely possible that this is the first data.
				$output .= '<br/>';

			$output .= __('Ends ', $this->pluginDomain);

			$output .= sp_get_end_date($post->ID); 
		}

		if ( $phone && sp_get_phone() != '') {
			if($output) 
				$output .= '<br/>';

			$output .= sp_get_phone(); 
		}
		if ( $cost && sp_get_cost() != '') {		
			if($output) 
				$output .= '<br/>';
			$output .= sp_get_cost(); 
		}

		echo $output;
	?>
	</div>
</li>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : '';