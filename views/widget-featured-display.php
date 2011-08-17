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
	$tribe_ecp = Events_Calendar_Pro::instance();
	reset($tribe_ecp->metaTags); // Move pointer to beginning of array.
	foreach($tribe_ecp->metaTags as $tag){
		$var_name = str_replace('_Event','',$tag);
		$event[$var_name] = getEventMeta( $post->ID, $tag, true );
	}

	$event = (object) $event; //Easier to work with.

	ob_start();
		post_class($alt_text,$post->ID);
	$class = ob_get_contents();
	ob_end_clean();
?>
	<div class="event">
		<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
	</div>
	<div class="when">
		<?php 
			echo sp_get_start_date( $post->ID, $start ); 

			if($event->AllDay && $start)
				echo ' <small>('.__('All Day',$this->pluginDomain).')</small>';
		?> 
	</div>
	<div class="loc">
		<?php
			if ( sp_get_city() != '' ) {
				echo sp_get_city() . ', ';
			}
			if (sp_get_region() != '') {
				echo sp_get_region() . ', '; 
			}
			if (sp_get_country() != '') {
				echo sp_get_country(); 
			}
		?>
	</div>
	<div class="event_body">
		<?php the_content('... More');?>
	</div>
<?php $alt_text = ( empty( $alt_text ) ) ? 'alt' : '';