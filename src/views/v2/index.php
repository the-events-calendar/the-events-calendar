<?php
get_header();

use Tribe\Events\Views\V2\View;

$view_slug = tribe_get_option( View::$option_default, 'default' );
$view = View::make( $view_slug );
echo $view->get_html();

get_footer();
