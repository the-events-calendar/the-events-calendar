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
	<div id='eventDetails' class="inside eventForm">
		<table cellspacing="0" cellpadding="0" id="EventInfo">
			<tr>
				<td colspan="2" class="tribe_sectionheader"><div class="tribe_sectionheader" style="padding: 6px 6px 0 0; font-size: 11px; margin: 0 10px;"><h4><?php _e('Auditing Information', 'tribe-events-calendar'); ?></h4></div></td>
			</tr>
			<tr>
			<td colspan="2">
			<table class="eventtable">
			<tr>
				<td width="25%"><?php _e('Created by:', 'tribe-events-calendar'); ?></td>
				<td><?php echo get_post_meta($post->ID, $post_type.'Origin', true); ?></td>
			</tr>
			<tr>
				<td valign="top"><?php _e('Audit Trail:', 'tribe-events-calendar'); ?></td>
				<td><?php echo $audit_trail_display; ?></td>
			</tr>
			</table>
			</td>
			</tr>
		</table>
	</div>
<?php } ?>