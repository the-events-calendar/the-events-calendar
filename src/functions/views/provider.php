<?php
use Tribe\Events\Views\V2\Service_Provider as Views;

/**
 * A simple way to verfify if View V2 is enabled
 *
 * @since  TBD
 *
 * @return bool
 */
function tribe_events_views_v2_is_enabled() {
	return Views::is_enabled();
}