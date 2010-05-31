<?php
$cat_id = get_query_var( 'cat' );
if( !$cat_id ) {
	$cat_id = $spEvents->eventCategory();
}
$link = get_category_link( $cat_id );
if( '' == get_option('permalink_structure') || 'off' == eventsGetOptionValue('useRewriteRules','on') ) {
	$link .= '&eventDisplay=month&eventDate=';
}

$link = events_get_dropdown_link_prefix();
?>
<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function($) {
		$('.<?php echo $prefix; ?>events-dropdown').change(function() {
			location.href = '<?php echo $link; ?>' + $('#<?php echo $prefix; ?>events-year').val() + '-' + $('#<?php echo $prefix; ?>events-month').val();
		});
	});
</script>
<select id='<?php echo $prefix; ?>events-month' name='EventJumpToMonth' class='<?php echo $prefix; ?>events-dropdown'>
	<?php echo $monthOptions; ?>
</select>
<select id='<?php echo $prefix; ?>events-year' name='EventJumpToYear' class='<?php echo $prefix; ?>events-dropdown'>
	<?php echo $yearOptions; ?>
</select>