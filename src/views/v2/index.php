<?php
get_header();

use Tribe\Events\Views\V2\View;
// @TODO: We're setting the div wrapper over here, but we'll need to revise this.
?>
<div class="tribe-common tribe-events">
<?php
$view_slug = tribe_get_option( View::$option_default, 'default' );
$view = View::make( $view_slug );
echo $view->get_html();
?>
</div>
<?php
get_footer();
