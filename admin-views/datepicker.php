<?php
/**
* Date picker form
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<form action="<?php echo tribe_get_dropdown_link_prefix(); ?>" method="get" id="<?php echo $prefix; ?>events-picker">
	<label for="<?php echo $prefix; ?>events-month" style="display: none;">Events Month</label>
	<select id='<?php echo $prefix; ?>events-month' name='EventJumpToMonth' class='<?php echo $prefix; ?>events-dropdown'>
		<?php echo $monthOptions; ?>
	</select>
	<label for="<?php echo $prefix; ?>events-year" style="display: none;">Events Year</label>
	<select id='<?php echo $prefix; ?>events-year' name='EventJumpToYear' class='<?php echo $prefix; ?>events-dropdown'>
		<?php echo $yearOptions; ?>
	</select>
	<noscript><input type="submit" value="&rarr;"></noscript>
</form>
