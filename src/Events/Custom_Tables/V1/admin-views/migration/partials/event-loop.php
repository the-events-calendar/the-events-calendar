<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string $template_directory The path to the template directory.
 */
?>
<ul class="tec-ct1-upgrade-events-container">
	<?php
	include($template_directory.'/partials/event-items.php');
	?>
</ul>