<?php
/**
 * View: Events Data Object.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/data.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 */

/**
 * Filters the data that will be printed for the View.
 *
 * @since 4.9.7
 *
 * @param array $data The data that will be printed for the current View.
 * @param string $view_slug The slug of the view currently being rendered.
 * @param Tribe\Events\Views\V2\View_Interface $view The View instance that is being rendered.
 */
$data = apply_filters( 'tribe_events_views_v2_view_data', $this->get_values(), $view_slug, $view );
?>
<script
	data-js="tribe-events-view-data"
	type="application/json"
>
	<?php echo json_encode( $data ); ?>
</script>
