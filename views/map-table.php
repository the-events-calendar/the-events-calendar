<?php

$result_count = count($data);
$counter = 0;
foreach($data as $event){ 
$counter++;
?>
	<div class="tribe-geo-result-entry<?php if( $result_count == $counter) echo ' tribe-geo-result-last'; ?>">
		<?php 
		if ( has_post_thumbnail( $event->ID ) ){ 		
		$result_thumb = wp_get_attachment_image_src(get_post_thumbnail_id( $event->ID ), 'medium');	
		?>
		<div class="tribe-geo-result-thumb">
			<a href="<?php echo tribe_get_event_link( $event->ID ) ?>" title="<?php echo $event->post_title; ?>">
				<img src="<?php echo $result_thumb[0]; ?>" alt="<?php echo $event->post_title; ?>" />
			</a>
		</div>
		<?php } ?>
		<div class="tribe-geo-result-data">
			<h2><a href="<?php echo tribe_get_event_link( $event->ID ) ?>"><?php echo $event->post_title; ?></a></h2>
			<?php if ( tribe_get_cost( $event->ID ) ) { ?>
			<div class="tribe-geo-result-cost">
				<span><?php echo tribe_get_cost( $event->ID ); ?></span>	
			</div>		
			<?php } ?>
			<div class="tribe-clear"></div>
			<?php 
			if ( tribe_is_multiday( $event->ID ) ) { 
			?>
			<span class="tribe-geo-result-date"><?php echo tribe_get_start_date( $event->ID, false ) . ' @ ' . tribe_get_start_date( $event->ID, false, 'g:i A' ) . ' - ' . tribe_get_end_date( $event->ID, false ); ?></span>	
			<?php } else { ?>
			<span class="tribe-geo-result-date"><?php echo tribe_get_start_date( $event->ID, false ) . ' @ ' . tribe_get_start_date( $event->ID, false, 'g:i A' ); ?></span>	
			<?php } ?>
			<span class="tribe-geo-result-venue"><?php echo '<strong>[' . round( $event->distance, 2 ) . ']</strong> ' . tribe_get_venue( $event->ID ) . ', ' . tribe_get_full_address( $event->ID ); ?></span>
			<div class="tribe-geo-result-excerpt">
			<?php 
			if ( has_excerpt( $event->ID ) )
				echo '<p>'. TribeEvents::truncate( $event->post_excerpt ) .'</p>';
			else
				echo '<p>'. TribeEvents::truncate( $event->post_content, 80 ) .'</p>';
			?>
			</div>					
		</div>
	</div>
<?php }
