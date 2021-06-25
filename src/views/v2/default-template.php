<?php
/**
 * View: Default Template for Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/default-template.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.0
 */

use Tribe\Events\Views\V2\Template_Bootstrap;

get_header();

$context_view = tribe_context()->get( 'view', 'default' );

/**
 * Provides an action that allows for the injection of HTML at the top of the template after the header.
 *
 * @since TBD
 *
 * @param string $context_view The string representation (slug) of the displayed view - "list".
 */
do_action( 'tribe_default_events_views_v2_template_after_header', $context_view );

echo tribe( Template_Bootstrap::class )->get_view_html();

/**
 * Provides an action that allows for the injections of HTML at the bottom of the template before the footer.
 *
 * @since TBD
 *
 * @param string $context_view The string representation (slug) of the displayed view - "list".
 */
do_action( 'tribe_default_events_views_v2_template_before_footer', $context_view );

get_footer();
