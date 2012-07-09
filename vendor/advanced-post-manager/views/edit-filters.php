<?php
$action_url = add_query_arg('post_type', $GLOBALS['typenow'], admin_url('edit.php') );
do_action('tribe-filters-box');
 ?>
<div id="tribe-filters" class="metabox-holder meta-box-sortables">
<div id="filters-wrap" class="postbox">
	<div class="handlediv" title="<?php _e('Click to toggle', $this->textdomain) ?>"></div>
	<h3 title="<?php _e('Click to toggle', $this->textdomain) ?>"><?php _e('Filters &amp; Columns', $this->textdomain ); ?></h3>
	<form id="the-filters" action="<?php echo $action_url; ?>" method="post">
		<div class="alignleft filters">
			<?php $this->filters->output_form(); ?>
		</div>
		<div class="alignright filters">
			<?php $this->columns->output_form(); ?>
		</div>
		<div class="alignleft actions">
			<input type="submit" name="tribe-apply" value="<?php _e('Apply', $this->textdomain) ?>" class="button-primary" />
			<input type="submit" name="tribe-clear" value="<?php _e('Clear', $this->textdomain) ?>" class="button-secondary" />
			<input type="submit" name="save" value="<?php _e('Save', $this->textdomain) ?>" class="button-secondary save" />
			<?php if ( $this->export ) : ?>
			<input type="submit" name="csv" value="Export" title="<?php _e('Export to CSV', $this->textdomain) ?>" class="button-secondary csv" />
			<?php endif; ?>
		</div>
		<div class="alignleft save-options">
			<label for="filter_name"><?php _e('Filter Name', $this->textdomain) ?> </label><input type="text" name="filter_name" value="" id="filter_name" />
			<input type="submit" name="tribe-save" value="<?php _e('Save', $this->textdomain) ?>" class="button-primary save" />
			<a href="#" id="cancel-save"><?php _e('Cancel', $this->textdomain) ?></a>
		</div>
	</form>
</div>
</div>