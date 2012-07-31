<?php
// Start of Presstrends Magic
function presstrends_plugin_tribe_events_calendar() {

	// PressTrends Account API Key
	$api_key = 'tije8ygaph33vjqfbnyv6irf0wzulmingvl2';
	$auth = 'emkw894xhz9vicapxnfeyvpa8secpqh23';
	
	// Start of Metrics
	global $wpdb;
	$data = get_transient( 'presstrends_data_tribe-events-calendar' );
	if (!$data || $data == ''){
		$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
		$url = $api_base . $auth . '/api/' . $api_key . '/';
		$data = array();
		$count_posts = wp_count_posts();
		$count_pages = wp_count_posts('page');
		$comments_count = wp_count_comments();
		$theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
		$plugin_count = count(get_option('active_plugins'));
		$all_plugins = get_plugins();
		$plugin_name = null;
		$plugin_name = '&';
		foreach($all_plugins as $plugin_file => $plugin_info) {
			$plugin_name .= $plugin_info['Name'];
			$plugin_name .= '&';
		}
		// This line has been edited from the default PressTrends code. Make sure to keep it edited with any future changes.
		$plugin_data = get_plugin_data( trailingslashit( dirname( dirname ( __FILE__ ) ) ) . 'the-events-calendar.php' );
		$plugin_version = $plugin_data['Version'];
		$posts_with_comments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='post' AND comment_count > 0");
		$comments_to_posts = number_format(($posts_with_comments / $count_posts->publish) * 100, 0, '.', '');
		$pingback_result = $wpdb->get_var('SELECT COUNT(comment_ID) FROM '.$wpdb->comments.' WHERE comment_type = "pingback"');
		$data['url'] = stripslashes(str_replace(array('http://', '/', ':' ), '', site_url()));
		$data['posts'] = $count_posts->publish;
		$data['pages'] = $count_pages->publish;
		$data['comments'] = $comments_count->total_comments;
		$data['approved'] = $comments_count->approved;
		$data['spam'] = $comments_count->spam;
		$data['pingbacks'] = $pingback_result;
		$data['post_conversion'] = $comments_to_posts;
		$data['theme_version'] = $plugin_version;
		$data['theme_name'] = urlencode($theme_data['Name']);
		$data['site_name'] = str_replace( ' ', '', get_bloginfo( 'name' ));
		$data['plugins'] = $plugin_count;
		$data['plugin'] = urlencode($plugin_name);
		$data['wpversion'] = get_bloginfo('version');
		foreach ( $data as $k => $v ) {
			$url .= $k . '/' . $v . '/';
		}
		$response = wp_remote_get( $url );
		set_transient('presstrends_data_tribe-events-calendar', $data, 60*60*24);
	}
}

// PressTrends WordPress Action
if ( tribe_get_option( 'sendPressTrendsData', false ) ) {
	add_action('admin_init', 'presstrends_plugin_tribe_events_calendar');
}