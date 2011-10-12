<?php
/**
 * Form for submitting support requests.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>
<div class="tribe-support-form">
<form method="post" action="<?php echo TribeEvents::$tribeUrl; ?>support/">
	<h3><?php _e('Need help? Send us a support request.','tribe-events-calendar-pro'); ?></h3>
	<input type="hidden" name="supportinfo" value="<?php TribeEventsProSupport::generateSupportHash(); ?>" />
	<div class="tribe-support-form-description">
		<p><?php printf(__('Tell us about the problem you\'re seeing in as much detail as possible. Please note that we\'ll also be sending some basic system information along with this request so that we can better diagnose the issue. If you would like to contact us without sending any system information, please visit our support page at %s. Thanks!','tribe-events-calendar-pro'), '<a href="'.TribeEvents::$tribeUrl.TribeEvents::$supportPath.'" target="_blank">'.TribeEvents::$tribeUrl.TribeEvents::$supportPath.'</a>'); ?></p>
		<textarea name="description"></textarea>
	</div>
	<div class="tribe-support-form-submit">
		<input type="submit" value="<?php _e('Send a Support Request','tribe-events-calendar-pro'); ?>"\>
	</div>
</form>
</div>