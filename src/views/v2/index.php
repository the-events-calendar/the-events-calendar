<?php
use Tribe\Events\Views\V2\Template_Bootstrap;
get_header();
echo tribe( Template_Bootstrap::class )->get_view_html();
get_footer();
