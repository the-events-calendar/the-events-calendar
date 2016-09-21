<?php
$table = new Tribe__Events__Aggregator__Record__List_Table();
$table->prepare_items();
?>

<?php echo Tribe__Events__Aggregator__Tabs__Scheduled::instance()->maybe_display_aggregator_missing_license_key_message(); ?>
<?php $table->views(); ?>
<form id="posts-filter" method="get">
<?php $table->nonce(); ?>
<?php $table->display(); ?>
</form>
