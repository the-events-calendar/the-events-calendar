<div style="float: right; width: 200px; margin: 15px;">
<div style="border: 1px solid #CCC; padding: 0 12px;">
<h3><?php _e('The Events Calendar'); ?></h3>
<?php 
$tec_info = file_get_contents( 'http://wpapi.org/api/plugin/the-events-calendar.php' );
$tec_info = unserialize($tec_info);
$rating = $tec_info['rating'] / 20;

$up_to_date = __('You are up to date!', 'tribe-events-calendar');
if ( version_compare( TribeEvents::VERSION, $tec_info['version'], '<' ) ) {
	$up_to_date = __('You need to upgrade!', 'tribe-events-calendar');
}

$news_rss = new DOMDocument();
$news_rss->load('http://tri.be/category/products/feed/');
$news_feed = array();
$i = 0;
foreach( $news_rss->getElementsByTagName( 'item' ) as $node ) {
	$item = array(
		'title' => $node->getElementsByTagName( 'title' )->item(0)->nodeValue,
		'link' => $node->getElementsByTagName( 'link' )->item(0)->nodeValue
	);
	$news_feed[] = $item;
	if (++$i >= 5) break;
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
	'title' => __('Events Calendar Pro', 'tribe_events_calendar'),
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
<p><?php echo( $up_to_date ); ?></p>
<p><b><?php _e('Latest Version:', 'tribe-events-calendar'); ?></b> <?php echo( $tec_info['version'] ); ?><br />
<b><?php _e('Author:', 'tribe-events-calendar'); ?></b> <?php echo( $tec_info['author']['name'] ); ?><br />
<b><?php _e('Requires:', 'tribe-events-calendar'); ?></b> <?php _e('WordPress ', 'tribe-events-calendar'); echo( $tec_info['requires'] ); ?>+<br /> 
<b><?php _e('Downloads:', 'tribe-events-calendar'); ?></b> <?php echo( number_format( $tec_info['total_downloads'] ) ); ?><br />
<a href="http://wordpress.org/extend/plugins/the-events-calendar/"><?php _e('Wordpress.org Plugin Page', 'tribe-events-calendar'); ?></a></p>
</div>
<h3><?php _e('Average Rating', 'tribe-events-calendar'); ?></h3>
<div class="star-holder">
<div class="star star-rating" style="width: <?php echo( $tec_info['rating'] ); ?>px"></div>
<div class="star star5"><img src="<?php echo( $this->pluginUrl . 'resources/images/star.gif' ); ?>" alt="5 stars" /></div>
<div class="star star4"><img src="<?php echo( $this->pluginUrl . 'resources/images/star.gif' ); ?>" alt="4 stars" /></div>
<div class="star star3"><img src="<?php echo( $this->pluginUrl . 'resources/images/star.gif' ); ?>" alt="3 stars" /></div>
<div class="star star2"><img src="<?php echo( $this->pluginUrl . 'resources/images/star.gif' ); ?>" alt="2 stars" /></div>
<div class="star star1"><img src="<?php echo( $this->pluginUrl . 'resources/images/star.gif' ); ?>" alt="1 star" /></div>
</div>
<?php printf( _n('Based on %s rating', 'Based on %s ratings', $tec_info['num_ratings'], 'tribe-events-calendar' ), $tec_info['num_ratings'] ); ?>
<br />
<a href="http://wordpress.org/extend/plugins/the-events-calendar/"><?php _e('Give us 5 stars!', 'tribe-events-calendar'); ?></a>
<h3><?php _e('Free Add-Ons', 'tribe-events-calendar'); ?></h3>
<ul>
<li><a href="http://wordpress.org/extend/plugins/advanced-post-manager/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">Advanced Post Manager</a></li>
<li>Event Importer (coming soon!)</li>
<li>Facebook Sync Events (coming soon!)</li>
</ul>
<h3><?php _e('Premium Add-Ons', 'tribe-events-calendar'); ?></h3>
<ul>
<li><a href="http://tri.be/wordpress-events-calendar-pro/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">Events Calendar Pro</a></li>
<li><a href="http://tri.be/shop/wordpress-eventbrite-tickets/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">Eventbrite Tickets (coming soon!)</a></li>
<li><a href="http://tri.be/shop/wordpress-community-events/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">Community Events (coming soon!)</a></li>
<li><a href="http://tri.be/shop/conference-manager/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">Conference Manager (coming later in 2012)</a></li>
<li><a href="http://tri.be/shop/wootickets/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin">WooTickets (coming later in 2012)</a></li>
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
<p><?php _e('Hi! Thank you so much for using the labor of our love. We are Modern Tribe and we are here to help you kick ass.', 'tribe-events-calendar'); ?></p>
<h3><?php _e('Getting Started', 'tribe-events-calendar'); ?></h3>
<?php echo( apply_filters( 'tribe-settings-help-getting-started-content', $getting_started_text ) ); ?>
<h3><?php _e('Resources to Help You Kick Ass', 'tribe-events-calendar'); ?></h3>
<ul class="admin-indent">
<li><a href="http://tri.be/support/documentation/?utm_source=helptab&utm_medium=supportlink&utm_campaign=plugin"><?php _e('Documentation', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/support/faqs/?utm_source=helptab&utm_medium=supportlink&utm_campaign=plugin"><?php _e('FAQ', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/help-video/?utm_source=helptab&utm_medium=supportlink&utm_campaign=plugin"><?php _e('Help Videos', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/tutorial/?utm_source=helptab&utm_medium=supportlink&utm_campaign=plugin"><?php _e('Tutorials', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/release-notes/?utm_source=helptab&utm_medium=supportlink&utm_campaign=plugin"><?php _e('Release Notes', 'tribe-events-calendar'); ?></a></li>
</ul>
<h3><?php _e('Everyone Needs a Buddy', 'tribe-events-calendar'); ?></h3>
<?php echo( apply_filters( 'tribe-settings-help-enb-content', $enb_text ) ); ?>
<h3><?php _e('Still Not Satisfied?', 'tribe-events-calendar'); ?></h3>
<?php echo( apply_filters( 'tribe-settings-sns-content', $sns_text ) ); ?>