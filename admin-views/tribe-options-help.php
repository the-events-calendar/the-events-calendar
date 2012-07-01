<?php

/**
 * variable setup
 */

$tec_info = wp_remote_get( apply_filters('tribe_help_tab_api_info_url', TribeEvents::INFO_API_URL) );
if ( !is_wp_error($tec_info) ) {
	$tec_info = $tec_info['body'];
	$tec_info = unserialize($tec_info);
	$rating = ( isset($tec_info['rating']) ) ? $tec_info['rating'] / 20 : _x('n/a', 'not available', 'tribe-events-calendar');
	$requires = ( isset($tec_info['requires']) ) ? $tec_info['requires'] : _x('n/a', 'not available', 'tribe-events-calendar');
	$version = ( isset($tec_info['version']) ) ? $tec_info['version'] : _x('n/a', 'not available', 'tribe-events-calendar');
	$total_downloads = ( isset($tec_info['total_downloads']) ) ? number_format( $tec_info['total_downloads'] ) : _x('n/a', 'not available', 'tribe-events-calendar');
	$up_to_date = ( isset($tec_info['version']) && version_compare( TribeEvents::VERSION, $tec_info['version'], '<' ) ) ? __('You need to upgrade!', 'tribe-events-calendar') : __('You are up to date!', 'tribe-events-calendar');
} else {
	$rating = $total_downloads = $requires = _x('n/a', 'not available', 'tribe-events-calendar');
	$up_to_date = '';
}

$news_rss = fetch_feed(TribeEvents::FEED_URL);
if ( !is_wp_error($news_rss) ) {
	$maxitems = $news_rss->get_item_quantity( apply_filters('tribe_help_tab_rss_max_items', 5) );
	$rss_items = $news_rss->get_items(0, $maxitems);
	$news_feed = array();
	if ( count($maxitems) > 0 ) {
		foreach( $rss_items as $item ) {
			$item = array(
				'title' => esc_html( $item->get_title() ),
				'link' => esc_url( $item->get_permalink() ),
			);
			$news_feed[] = $item;
		}
	}
}

$ga_query_string = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';

$free_add_ons = array();
$free_add_ons[] = array(
	'title' => __('Advanced Post Manager', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_apm_wp_url', 'http://wordpress.org/extend/plugins/advanced-post-manager/'),
);
$free_add_ons[] = array(
	'title' => __('Event Importer', 'tribe_events_calendar'),
	'coming_soon' => true,
);
$free_add_ons[] = array(
	'title' => __('Facebook Sync Events', 'tribe_events_calendar'),
	'coming_soon' => true,
);
$free_add_ons = (array) apply_filters( 'tribe_help_tab_free_addons', $free_add_ons );

$premium_add_ons = array();
$premium_add_ons[] = array(
	'title' => __('The Events Calendar Pro', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_ecp_tribe_url', 'http://tri.be/wordpress-events-calendar-pro/'.$ga_query_string),
);
$premium_add_ons[] = array(
	'title' => __('Eventbrite Tickets', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_eventbrite_tribe_url', 'http://tri.be/shop/wordpress-eventbrite-tickets/'.$ga_query_string),
	'coming_soon' => true,
);
$premium_add_ons[] = array(
	'title' => __('Community Events', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_community_events_tribe_url', 'http://tri.be/shop/wordpress-community-events/'.$ga_query_string),
	'coming_soon' => true,
);
$premium_add_ons[] = array(
	'title' => __('WooTickets', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_wootickets_tribe_url', 'http://tri.be/shop/wootickets/'.$ga_query_string),
	'coming_soon' => __('(coming later in 2012)', 'tribe_events_calendar'),
);
$premium_add_ons[] = array(
	'title' => __('Conference Manager', 'tribe_events_calendar'),
	'link' => apply_filters('tribe_help_tab_conference_manager_tribe_url', 'http://tri.be/shop/conference-manager/'.$ga_query_string),
	'coming_soon' => __('(coming later in 2012)', 'tribe_events_calendar'),
);
$premium_add_ons = (array) apply_filters( 'tribe_help_tab_premium_addons', $premium_add_ons );


$resources = array();
$resources[] = array(
	'title' => __('Documentation', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_documentation_url', 'http://tri.be/support/documentation/'.$ga_query_string),
);
$resources[] = array(
	'title' => __('FAQ', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_faq_url', 'http://tri.be/support/faqs/'.$ga_query_string),
);
$resources[] = array(
	'title' => __('Help', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_help_video_url', 'http://tri.be/category/products/help-video/'.$ga_query_string),
);
$resources[] = array(
	'title' => __('Tutorials', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_tutorials_url', 'http://tri.be/category/products/tutorial/'.$ga_query_string),
);
$resources[] = array(
	'title' => __('Release Notes', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_release_notes_url', 'http://tri.be/category/products/release-notes/'.$ga_query_string),
);
$resources[] = array(
	'title' => __('Forums', 'tribe-events-calendar'),
	'link' => apply_filters('tribe_help_tab_forums_url', 'http://wordpress.org/support/plugin/the-events-calendar/'),
);
$resources = (array) apply_filters( 'tribe_help_tab_resources', $resources );


$getting_started_text = sprintf( __('If this is your first time using The Events Calendar, you\'re in for a treat. The more adventurous users can jump right into it by finding the "Events" section in the admin menu to the left of this message and getting down to it. For those who like to dip their toes before diving in full-on, we\'ve got you covered too. First things first: visit our %s, designed with folks exactly like yourself in mind and meant to familiarize you with the plugin\'s basics. From there, the Resources listed below (meant to help you kick ass, of course) should keep up the momentum.', 'tribe-events-calendar'), sprintf( '<a href="http://tri.be/support/documentation/events-calendar-pro-new-user-primer/' .$ga_query_string .'">%s</a>', __('new user primer', 'tribe-events-calendar') ) );
$getting_started_text = apply_filters( 'tribe_help_tab_getting_started_text', $getting_started_text );

$enb_text[] = sprintf( __('We love all our users and want to help free & PRO customers alike. If you\'re running the latest version of The Events Calendar and are having problems, post a thread the %s at WordPress.org. We hit the forum a few times a week and do what we can to assist users.', 'tribe-events-calendar'), sprintf( '<a href="http://wordpress.org/tags/the-events-calendar/' .$ga_query_string .'&forum_id=10">%s</a>', __('forum for The Events Calendar', 'tribe-events-calendar') ) );


$enb_text[] = sprintf( __('%sA few things to keep in mind before posting:%s', 'tribe-events-calendar'), '<p class="admin-indent">', '</p><ul class="admin-list">' );
$enb_text[] = sprintf( __('%sLook through the recent active threads before posting a new one and check that there isn\'t already a discussion going on your issue.%s', 'tribe-events-calendar'), '<li>', '</li>' );
$enb_text[] = sprintf( __('%sA good way to help us out before posting is to check whether the issue is a conflict with another plugin or your theme. This can be tested relatively easily on a staging site by deactivating other plugins one-by-one, and reverting to the default 2011 theme as needed, to see if conflicts can be easily identified. If so, please note that when posting your thread.%s', 'tribe-events-calendar'), '<li>', '</li>' );
$enb_text[] = sprintf( __('%sSometimes, just resaving your permalinks (under Settings -> Permalinks) can resolve events-related problems on your site. It is worth a shot before creating a new thread.%s', 'tribe-events-calendar'), '<li>', '</li>' );
$enb_text[] = sprintf( __('%sMake sure you\'re running The Events Calendar, rather than Events Calendar. They\'re two separate plugins :)%s', 'tribe-events-calendar'), '<li>', '</li></ul>' );
$enb_text[] = sprintf( __('%sWhile our team is happy to help with bugs and provide light integration tips for users of The Events Calendar, we\'re not able to provide customization tips or assist in integrating with 3rd party plugins on the WordPress.org forums.%s', 'tribe-events-calendar'), '<p class="admin-indent">', '</p>' );
$enb_text = implode( $enb_text );
$sns_text = sprintf( __('%sShoot us an email to %s or tweet to %s and tell us why. We\'ll do what we can to make it right.%s', 'tribe-events-calendar'), '<p class="admin-indent">', sprintf( '<a href="mailto:pro@tri.be">%s</a>', __('pro@tri.be', 'tribe-events-calendar') ), sprintf( '<a href="http://www.twitter.com/moderntribeinc">%s</a>', __('@moderntribeinc', 'tribe-events-calendar') ), '</p>' );
$more_text = __('More...', 'tribe-events-calendar');
?>

<div id="tribe-help-general">
	<div id="modern-tribe-info">
		<img src="<?php echo plugins_url('resources/images/modern-tribe.png', dirname(__FILE__)) ?>" alt="Modern Tribe Inc." title="Modern Tribe Inc.">

		<p><?php _e('Hi! Thank you so much for using the labor of our love. We are Modern Tribe and we are here to help you kick ass.', 'tribe-events-calendar'); ?></p>

		<h3><?php _e('Getting Started', 'tribe-events-calendar'); ?></h3>

		<?php echo( apply_filters( 'tribe_help_tab_getting_started_content', $getting_started_text ) ); ?>
	</div>

	<h3><?php _e('Resources to Help You Kick Ass', 'tribe-events-calendar'); ?></h3>

	<ul class="admin-indent">
	<?php foreach ($resources as $resource) :
		echo '<li>';
		if ( isset($resource['link']) ) echo '<a href="'.$resource['link'].'">';
		echo $resource['title'];
		if ( isset($resource['link']) ) echo '</a>';
		echo '</li>';
	endforeach; ?>
	</ul>

	<h3><?php _e('Everyone Needs a Buddy', 'tribe-events-calendar'); ?></h3>
	<?php echo( apply_filters( 'tribe_help_tab_enb_content', $enb_text ) ); ?>

	<h3><?php _e('Still Not Satisfied?', 'tribe-events-calendar'); ?></h3>
	<?php echo( apply_filters( 'tribe_help_tab_sns_content', $sns_text ) ); ?>
</div>


<div id="tribe-help-sidebar">
	<div id="tribe-help-plugin-info">
		<h3><?php _e('The Events Calendar', 'tribe-events-calendar'); ?></h3>


		<p><?php echo $up_to_date; ?></p>
		<p><b><?php _e('Latest Version:', 'tribe-events-calendar'); ?></b> <?php echo $version; ?><br />
		<b><?php _e('Author:', 'tribe-events-calendar'); ?></b> <?php _e('Modern Tribe Inc', 'tribe-events-calendar'); ?><br />
		<b><?php _e('Requires:', 'tribe-events-calendar'); ?></b> <?php _e('WordPress ', 'tribe-events-calendar'); echo $requires; ?>+<br />
		<a href="<?php echo apply_filters('tribe_help_tab_wp_plugin_url', TribeEvents::WP_PLUGIN_URL); ?>"><?php _e('Wordpress.org Plugin Page', 'tribe-events-calendar'); ?></a></p>
	</div>

	<h3><?php _e('Average Rating', 'tribe-events-calendar'); ?></h3>

	<?php if ($rating != _x('n/a', 'not available', 'tribe-events-calendar') ) :  ?>
		<div class="star-holder">
			<div class="star star-rating" style="width: <?php echo( $tec_info['rating'] ); ?>px"></div>
		</div>
		<?php printf( _n('Based on %d rating', 'Based on %d ratings', $tec_info['num_ratings'], 'tribe-events-calendar' ), $tec_info['num_ratings'] ); ?>
	<?php else : ?>
		<div class="no-rating-available">
			<?php _e('Rating currently unavailable :(', 'tribe-events-calendar'); ?>
		</div>
	<?php endif; ?>


	<br />
	<a href="<?php echo apply_filters('tribe_help_tab_wp_plugin_url', TribeEvents::WP_PLUGIN_URL); ?>"><?php _e('Give us 5 stars!', 'tribe-events-calendar'); ?></a>

	<h3><?php _e('Free Add-Ons', 'tribe-events-calendar'); ?></h3>
	<ul>
	<?php foreach ($free_add_ons as $addon) :
		echo '<li>';
		if ( isset($addon['link']) ) echo '<a href="'.$addon['link'].'">';
		echo $addon['title'];
		if ( isset($addon['coming_soon']) ) echo ( is_string($addon['coming_soon']) ) ? ' '.$addon['coming_soon'] : ' '.__('(Coming Soon!)', 'tribe-events-calendar');
		if ( isset($addon['link']) ) echo '</a>';
		echo '</li>';
	endforeach; ?>
	</ul>


	<h3><?php _e('Premium Add-Ons', 'tribe-events-calendar'); ?></h3>
	<ul>
	<?php foreach ($premium_add_ons as $addon) :
		echo '<li>';
		if ( isset($addon['link']) ) echo '<a href="'.$addon['link'].'">';
		echo $addon['title'];
		if ( isset($addon['coming_soon']) ) echo ( is_string($addon['coming_soon']) ) ? ' '.$addon['coming_soon'] : ' '.__('(Coming Soon!)', 'tribe-events-calendar');
		if ( isset($addon['link']) ) echo '</a>';
		echo '</li>';
	endforeach; ?>
	</ul>


	<h3><?php _e('News and Tutorials', 'tribe-events-calendar'); ?></h3>
	<ul>
	<?php
	foreach ( $news_feed as $item ) {
		echo( '<li><a href="' . $item['link'] . '?utm_source=helptab&utm_medium=news&utm_campaign=plugin">' . $item['title'] . '</a></li>' );
	}
	echo '<li><a href="http://tri.be/category/products/?utm_source=helptab&utm_medium=news&utm_campaign=plugin">' . $more_text . '</a></li>';
	?>
	</ul>

</div>