<?php
use Tribe\Events\Views\V2\Template_Bootstrap;
// @TODO: We're setting the div wrapper over here, but we'll need to revise this.
get_header();

echo tribe( Template_Bootstrap::class )->get_view_html();

get_footer();
