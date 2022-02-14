<?php
// Ensure the Blocks Editor is active.
add_filter( 'tribe_editor_should_load_blocks', '__return_true', 200 );
add_filter( 'tribe_events_blocks_editor_is_on', '__return_true', 200 );

// Now re-register the Blocks Editor provider to make sure the Blocks Editor classes will be loaded.
tribe()->register( Tribe__Events__Editor__Provider::class );
