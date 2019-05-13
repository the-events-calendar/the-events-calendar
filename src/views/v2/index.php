<?php
get_header();

use Tribe\Events\Views\V2\View;
// @TODO: Temporarily we set inline styles to make it match with the new design
// @TODO: We need to give these styles depending if they're using tribe styles or skeleton afterwards.
?>
<div class="tribe-common tribe-events" style="max-width: 1176px; width: 100%; margin: 0 auto;">
<?php
$view_slug = tribe_get_option( View::$option_default, 'default' );
$view = View::make( $view_slug );
echo $view->get_html();
?>
</div>
<?php
get_footer();
