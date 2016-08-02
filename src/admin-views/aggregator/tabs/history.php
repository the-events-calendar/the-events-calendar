<?php
$table = new Tribe__Events__Aggregator__Record__List_Table();
$table->prepare_items();
?>

<?php $table->views(); ?>

<form id="posts-filter" method="get">
<?php $table->display(); ?>
</form>
