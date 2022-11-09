<?php
// Let's make sure to set rewrite rules.
global $wp_rewrite;
$wp_rewrite->permalink_structure = '/%postname%/';
$wp_rewrite->rewrite_rules();
