<?php
/**
 * View: Events Data Object.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/data.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 5.0.0
 * @var string                               $view_slug The slug of the view currently being rendered.
 * @var Tribe\Events\Views\V2\View_Interface $view      The View instance that is being rendered.
 *
 */

$data = $this->get_values();

/**
 * For very specific performance and security reasons we choose to not expose the full WP_Post object by default.
 * The following filter will allow by passing true, that the full WP_Post object gets exposed.
 *
 * @since 5.0.0
 *
 * @param boolean                              $should_expose_post_object If we should expose the events object or not.
 * @param array                                $data                      Data that will be exposed.
 * @param string                               $view_slug                 The slug of the view currently being rendered.
 * @param Tribe\Events\Views\V2\View_Interface $view                      The View instance that is being rendered.
 */
$should_expose_post_object = apply_filters( 'tribe_events_views_v2_view_data_should_expose_post_object', false, $data, $view_slug, $view );

if ( ! $should_expose_post_object ) {
	array_walk_recursive( $data, function ( &$value, $key ) {
		if ( $value instanceof WP_Post ) {
			$value = $value->ID;
		}
	} );
}

/**
 * Filters the data that will be printed for the View.
 *
 * @since 4.9.7
 *
 * @param array                                $data      The data that will be printed for the current View.
 * @param string                               $view_slug The slug of the view currently being rendered.
 * @param Tribe\Events\Views\V2\View_Interface $view      The View instance that is being rendered.
 */
$data = apply_filters( 'tribe_events_views_v2_view_data', $data, $view_slug, $view );

/**
 * Allows the filtering of the JSON options for testing and development purposes of the JSON encode options for the
 * view data JSON passed into the manager.
 *
 * @since 5.5.0
 *
 * @param int                                  $options   The Options passed into the json_encode.
 */
$json_options = apply_filters( 'tribe_events_views_v2_view_data_json_encode_options', 0 );
?>
<script data-js="tribe-events-view-data" type="application/json">
	<?php echo wp_json_encode( $data, $json_options ); ?>
</script>
