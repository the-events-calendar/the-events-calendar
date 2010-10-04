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
// 			'$event->Venue',
// 			'$event->Country',
// 			'$event->Address',
// 			'$event->City',
// 			'$event->State',
// 			'$event->Province',
// 			'$event->Zip',
// 			'$event->ShowMapLink',
// 			'$event->ShowMap',
// 			'$event->Cost',
// 			'$event->Phone',

	$event = array();
	global $sp_ecp;
	reset($sp_ecp->metaTags); // Move pointer to beginning of array.
	foreach($sp_ecp->metaTags as $tag){
		$var_name = str_replace('_Event','',$tag);
		$event[$var_name] = get_post_meta( $post->ID, $tag, true );
	}

	$event = (object) $event; //Easier to work with.

	ob_start();
		post_class($alt_text,$post->ID);
	$class = ob_get_contents();
	ob_end_clean();
?>
<li <?php echo $class ?>>
	<div class="when">
		<?php echo sp_get_start_date( $post->ID, $start ); ?>
	</div>
	<div class="event">
		<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="loc"><?php
		$space = false;
		$output = '';

		if ( $venue && $event->Venue != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= $event->Venue; 
		}

		if ( $address && $event->Address != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= $event->Address; 
		}

		if ( $city && $event->City != '' ) {
			$space = true;
			$output = $event->City . ', ';
		}
		if ( $state || $province ) {
			if ( $event->Country == "United States" &&  $event->State != '') {
				$space = true;
				$output .= $event->State;
			} elseif  ( $event->Province != '' ) {
				$space = true;
				$output .= $event->Province;
			}
		} else {
			$output = rtrim( $output, ', ' );
		}

		if ( $zip && $event->Zip != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= $event->Zip;
			$space = true;
		}

		if ( $country && $event->Country != '') {
			$output .= ( $space ) ? '<br />' : '';
			$output .= $event->Country; 
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

		if ( $phone && $event->Phone != '') {
			if($output) 
				$output .= '<br/>';

			$output .= $event->Phone; 
		}
		if ( $cost && $event->Cost != '') {		
			if($output) 
				$output .= '<br/>';
			$output .= $event->Cost; 
		}

		echo $output;
	?>
	</div>
</li>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : '';