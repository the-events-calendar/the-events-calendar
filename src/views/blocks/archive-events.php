<?php
/**
 * View: Default Template for the Archive of Events on FSE
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/archive-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 5.13.0
 */

use Tribe\Events\Views\V2\Assets as Event_Assets;
use Tribe\Events\Views\V2\Template_Bootstrap;

tribe_asset_enqueue_group( Event_Assets::$group_key );
?>

<div class="tribe-block tec-block__archive-events">
	<?php echo tribe( Template_Bootstrap::class )->get_view_html(); ?>
</div>
