<?php
/**
* Events audit trial metabox
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( is_admin() ){
	
	//$postid = intval($_GET['post']);
	
	global $post;
	
	if ( isset($post->ID) && isset($post->post_type) ) {
		if ( $post->post_type == self::POSTTYPE ) {
			$post_type = '_Event';
		} elseif ( $post->post_type == self::VENUE_POST_TYPE ) {
			$post_type = '_Venue';
		} elseif ( $post->post_type == self::ORGANIZER_POST_TYPE ) {
			$post_type = '_Organizer';
		} else {
			return;
		}
	}
	
	$audit_trail = get_post_meta($post->ID, $post_type.'AuditTrail', true);
	
	$audit_trail_display = '';
	
	foreach( $audit_trail as $at ){
	
		$audit_trail_display .= date('Y-m-d g:i:s a', $at[1]) .' : '. $at[0].'<br>';
	
	}
	
	?>
	<div id='eventDetails' class="inside eventForm bubble">
		<table cellspacing="0" cellpadding="0" id="EventInfo">
			<tr>
				<td colspan="2" class="tribe_sectionheader"><h4 class="event-time"><?php _e('Auditing Information', 'tribe-events-calendar'); ?></h4></td>
			</tr>
			<tr>
				<td width="10%"><?php _e('Created by:', 'tribe-events-calendar'); ?></td>
				<td><?php echo get_post_meta($post->ID, $post_type.'Origin', true); ?></td>
			</tr>
			<tr>
				<td valign="top"><?php _e('Audit Trail:', 'tribe-events-calendar'); ?></td>
				<td><?php echo $audit_trail_display; ?></td>
			</tr>
	
		</table>
	</div>
<?php } ?>