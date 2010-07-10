<?php
delete_option( 'sp_events_calendar_options' );
global $wp_rewrite;
$wp_rewrite->flush_rules();