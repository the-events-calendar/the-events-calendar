<?php return 'LEFT JOIN test_postmeta AS tribe_event_status_filterbar_alias
			ON ( test_posts.ID = tribe_event_status_filterbar_alias.post_id
			AND tribe_event_status_filterbar_alias.meta_key = \'_tribe_events_status\' )';
