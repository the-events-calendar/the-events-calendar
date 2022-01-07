<?php
/**
 * View: Events JSON-LD Data.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/json-ld-data.php
 *
 * See more documentation about our views templating system.
 *
 * @var string                               $view_slug    The slug of the view currently being rendered.
 * @var Tribe\Events\Views\V2\View_Interface $view         The View instance that is being rendered.
 * @var string                               $json_ld_data The View JSON-LD data markup.
 * @var boolean                              $jsonld_enable Whether the JSON-LD data output is enabled or not.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.12.1
 *
 * @since 5.0.2
 * @since 5.12.1 Add and observe the $jsonld_enable variable.
 */

if ( isset( $jsonld_enable ) && ! $jsonld_enable ) {
	return;
}

/**
 * Filters the JSON-LD data markup that will be printed for the View.
 *
 * While this filter controls the markup at the view level, other earlier filters are available in the View template vars
 * and in the code responsible for the JSON-LD data.
 *
 * @since 5.0.2
 *
 * @param array                                $json_ld_data The JSON-LD data markup for the current View and Context.
 * @param string                               $view_slug    The slug of the view currently being rendered.
 * @param Tribe\Events\Views\V2\View_Interface $view         The View instance that is being rendered.
 */
$json_ld_data = apply_filters( 'tribe_events_views_v2_view_json_ld_markup', $json_ld_data, $view_slug, $view );

echo $json_ld_data;
