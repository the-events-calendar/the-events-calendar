<?php
$tab = $this->tabs->get_active();
?>

<form method="POST" class="tribe-ea-form tribe-ea-tab <?php echo sanitize_html_class( 'tribe-ea-tab-' . $tab->get_slug() ); ?>">
	<?php $tab->render(); ?>
</form>
