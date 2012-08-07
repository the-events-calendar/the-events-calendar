<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2>Attendees</h2>
</div>

<form id="topics-filter" method="get">
	<?php $attendees_table->search_box( "Search", "s" ); ?>
	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	<input type="hidden" name="post_type" value="topic"/>
	<?php $attendees_table->display() ?>
</form>
