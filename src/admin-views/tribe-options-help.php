<?php

/**
 * variable setup
 */

$tec_info = wp_remote_get(
/**
 * Filter the tribe info API url
 *
 * @param string $url
 */
apply_filters( 'tribe_help_tab_api_info_url', Tribe__Events__Main::INFO_API_URL ), array(
		'timeout' => 15, //seconds
		'headers' => array( 'Accept' => 'application/json' ),
	)
);
if ( ! is_wp_error( $tec_info ) ) {
	$tec_info = $tec_info['body'];
	$tec_info = unserialize( $tec_info );
	if ( isset( $tec_info['rating'] ) ) {
		$rating = $tec_info['rating'];
	}
	if ( isset( $tec_info['num_ratings'] ) ) {
		$num_rating = $tec_info['num_ratings'];
	}
	if ( isset( $tec_info['requires'] ) ) {
		$requires = $tec_info['requires'];
	}
	if ( isset( $tec_info['version'] ) ) {
		$version = $tec_info['version'];
	}
	$total_downloads = ( isset( $tec_info['total_downloads'] ) ) ? number_format( $tec_info['total_downloads'] ) : _x( 'n/a', 'not available', 'the-events-calendar' );
	$up_to_date      = ( isset( $tec_info['version'] ) && version_compare( Tribe__Events__Main::VERSION, $tec_info['version'], '<' ) ) ? __( 'You need to upgrade!', 'the-events-calendar' ) : __( 'You are up to date!', 'the-events-calendar' );
}

$news_rss = fetch_feed( Tribe__Events__Main::FEED_URL );
if ( ! is_wp_error( $news_rss ) ) {
	$maxitems  = $news_rss->get_item_quantity(
		/**
		 * Filter the maximum number of items returned from the tribe news feed
		 *
		 * @param int $max_items default 5
		 */
		apply_filters( 'tribe_help_tab_rss_max_items', 5 ) );
	$rss_items = $news_rss->get_items( 0, $maxitems );
	$news_feed = array();
	if ( count( $maxitems ) > 0 ) {
		foreach ( $rss_items as $item ) {
			$item        = array(
				'title' => esc_html( $item->get_title() ),
				'link'  => esc_url( $item->get_permalink() ),
			);
			$news_feed[] = $item;
		}
	}
}

$ga_query_string = '?utm_source=helptab&utm_medium=plugin-tec&utm_campaign=in-app';

$premium_add_ons   = array();
$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar PRO', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar PRO product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_ecp_tribe_url', 'http://m.tri.be/dr' ),
);
$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: Eventbrite Tickets', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: Eventbrite Tickets product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_eventbrite_tribe_url', 'http://m.tri.be/ds' ),
);
$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: Community Events', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: Community Events product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_community_events_tribe_url', 'http://m.tri.be/dt' ),
);
$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: Facebook Events', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: Facebook Events product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_facebook_events_tribe_url', 'http://m.tri.be/du' ),
);
$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: WooCommerce Tickets', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: WooCommerce Tickets product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_wootickets_tribe_url', 'http://m.tri.be/dv' ),
);

$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: EDD Tickets', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: EDD Tickets product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_eddtickets_tribe_url', 'http://m.tri.be/dw' ),
);

$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: WPEC Tickets', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: WPEC Tickets product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_wpectickets_tribe_url', 'http://m.tri.be/dx' ),
);

$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: Shopp Tickets', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: Shopp Tickets product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_shopptickets_tribe_url', 'http://m.tri.be/dy' ),
);

$premium_add_ons[] = array(
	'title' => __( 'The Events Calendar: Filter Bar', 'the-events-calendar' ),
	/**
	 * Filter the url to The Events Calendar: Filter Bar product page
	 *
	 * @param string $url
	 */
	'link'  => apply_filters( 'tribe_help_tab_filterbar_tribe_url', 'http://m.tri.be/hu' ),
);

/**
 * Filter the array of premium addons upsold on the sidebar of the Settings > Help tab
 *
 * @param array $premium_add_ons
 */
$premium_add_ons   = (array) apply_filters( 'tribe_help_tab_premium_addons', $premium_add_ons ); // TODO should we replace this with an RSS feed??

$getting_started_text = __( "If you're looking for help with The Events Calendar, you've come to the right place. We are committed to helping make your calendar be spectacular... and hope the resources provided below will help get you there.", 'the-events-calendar' );

/**
 * Filter the text inside the box at the top of the Settings > Help tab
 *
 * @param string $getting_started_text
 */
$getting_started_text = apply_filters( 'tribe_help_tab_getting_started_text', $getting_started_text );

$intro_text[] = '<p>' . __( "If this is your first time using The Events Calendar, you're in for a treat and are already well on your way to creating a first event. Here are some basics we've found helpful for users jumping into it for the first time:", 'the-events-calendar' ) . '</p>';
$intro_text[] = '<ul>';
$intro_text[] = '<li>';
$intro_text[] = sprintf( __( '%sOur New User Primer%s was designed for folks in your exact position. Featuring both step-by-step videos and written walkthroughs that feature accompanying screenshots, the primer aims to take you from zero to hero in no time.', 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'knowledgebase/new-user-primer-the-events-calendar-and-events-calendar-pro/' . $ga_query_string ) . '" target="blank">', '</a>' );
$intro_text[] = '</li><li>';
$intro_text[] = sprintf( __( '%sInstallation/Setup FAQs%s from our support page can help give an overview of what the plugin can and cannot do. This section of the FAQs may be helpful as it aims to address any basic install questions not addressed by the new user primer.', 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'knowledgebase/' . $ga_query_string ) . '" target="blank">', '</a>' );
$intro_text[] = '</li></ul><p>';
$intro_text[] = __( "Otherwise, if you're feeling adventurous, you can get started by heading to the Events menu and adding your first event.", 'the-events-calendar' );
$intro_text[] = '</p>';
$intro_text   = implode( $intro_text );

$support_text[] = '<p>' . sprintf( __( "We've redone our support page from the ground up in an effort to better help our users. Head over to our %sSupport Page%s and you'll find lots of great resources, including:", 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'support/' . $ga_query_string ) . '" target="blank">', '</a>' ) . '</p>';
$support_text[] = '<ul><li>';
$support_text[] = sprintf( __( '%sTemplate tags, functions, and hooks & filters%s for The Events Calendar &amp; Events Calendar PRO', 'the-events-calendar' ), '<a href="http://m.tri.be/fk" target="blank">', '</a>' );
$support_text[] = '</li><li>';
$support_text[] = sprintf( __( '%sFrequently Asked Questions%s ranging from the most basic setup questions to advanced themer tweaks', 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'knowledgebase/' . $ga_query_string ) . '" target="blank">', '</a>' );

$support_text[] = '</li><li>';
$support_text[] = sprintf( __( '%sTutorials%s written by both members of our team and users from the community, covering custom queries, integration with third-party themes and plugins, etc.', 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tribeUrl . 'the-events-calendar-for-wordpress-tutorials/' . $ga_query_string ) . '" target="blank">', '</a>' );
$support_text[] = '</li><li>';
$support_text[] = __( "Release notes for painting an overall picture of the plugin's lifecycle and when features/bug fixes were introduced.", 'the-events-calendar' );
$support_text[] = '</li><li>';
$support_text[] = sprintf( __( "%sAdd-on documentation%s for all of Modern Tribe's official extensions for The Events Calendar (including WooTickets, Community Events, Eventbrite Tickets, Facebook Events, etc)", 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'knowledgebase-category/primers/' ) . '" target="blank">', '</a>' );
$support_text[] = '</li></ul>';
$support_text[] = '<p>' . sprintf( __( "We've also got a %sModern Tribe UserVoice%s page where we're actively watching for feature ideas from the community. If after playing with the plugin and reviewing the resources above, you're finding a feature isn't present that should be, let us know. Vote up existing feature requests or add your own, and help us shape the future of the products business in a way that best meets the community's needs.", 'the-events-calendar' ), '<a href="http://tribe.uservoice.com/" target="blank">', '</a>' ) . '</p>';
$support_text   = implode( $support_text );


$forum_text[] = '<p>' . sprintf( __( 'Written documentation can only take things so far...sometimes, you need help from a real person. This is where our %ssupport forums%s come into play.', 'the-events-calendar' ), '<a href="http://wordpress.org/support/plugin/the-events-calendar" target="blank">', '</a>' ) . '</p>';
$forum_text[] = '<p>' . sprintf( __( "Users of the free The Events Calendar should post their support concerns to the plugin's %sWordPress.org support forum%s. While we are happy to help identify and fix bugs that are reported at WordPress.org, please make sure to read our %ssupport expectations sticky thread%s before posting so you understand our limitations.", 'the-events-calendar' ), '<a href="http://wordpress.org/support/plugin/the-events-calendar" target="blank">', '</a>', '<a href="http://wordpress.org/support/topic/welcome-the-events-calendar-users-read-this-first?replies=1" target="blank">', '</a>' ) . '</p>';
$forum_text[] = '<p>' . __( "We hit the WordPress.org forum throughout the week, watching for bugs. If you report a legitimate bug that we're able to reproduce, we will log it and patch for an upcoming release. However we are unfortunately unable to provide customization tips or assist in integrating with 3rd party plugins or themes.", 'the-events-calendar' ) . '</p>';
$forum_text[] = '<p>' . sprintf( __( "If you're a user of The Events Calendar and would like more support, please %spurchase a PRO license%s. We hit the PRO forums daily, and can provide a deeper level of customization/integration support for paying users than we can on WordPress.org.", 'the-events-calendar' ), '<a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'product/wordpress-events-calendar-pro/' . $ga_query_string ) . '" target="blank">', '</a>' ) . '</p>';
$forum_text   = implode( $forum_text );


$outro_text = '<p>' . sprintf( __( 'If you find that you aren\'t getting the level of service you\'ve come to expect from Modern Tribe, shoot us an email at %1$s or tweet %2$s and tell us why. We\'ll do what we can to make it right.', 'the-events-calendar' ), '<a href="mailto:pro@tri.be">pro@tri.be</a>', '<a href="http://www.twitter.com/moderntribeinc" target="blank">@moderntribeinc</a>' ) . '</p>';
$more_text  = __( 'More...', 'the-events-calendar' );


?>

<div id="tribe-help-general">
	<div id="modern-tribe-info">
		<img src="<?php echo esc_url( plugins_url( 'resources/images/modern-tribe@2x.png', dirname( __FILE__ ) ) ); ?>" alt="Modern Tribe Inc." title="Modern Tribe Inc.">

		<p><?php esc_html_e( 'Hi! We are Modern Tribe and we are here to help you be awesome. Thanks so much for installing our labor of love!', 'the-events-calendar' ); ?></p>
		<?php
		/**
		 * Filter the text inside the box at the top of the Settings > Help tab
		 *
		 * @param string $getting_started_text
		 */
		echo apply_filters( 'tribe_help_tab_getting_started_content', $getting_started_text ); ?>
	</div>

	<div class="tribe-settings-form-wrap">

		<h3><?php esc_html_e( 'Getting Started', 'the-events-calendar' ); ?></h3>
		<?php
		/**
		 * Filter the "Getting Started" text on the Settings > Help tab
		 *
		 * @param string $intro_text
		 */
		echo apply_filters( 'tribe_help_tab_introtext', $intro_text );
		?>

		<h3><?php esc_html_e( 'Support Resources To Help You Be Awesome', 'the-events-calendar' ); ?></h3>
		<?php
		/**
		 * Filter the "Support Resources To Help You Be Awesome" text on the Settings > Help tab
		 *
		 * @param string $intro_text
		 */
		echo apply_filters( 'tribe_help_tab_supporttext', $support_text );
		?>

		<h3><?php esc_html_e( 'Forums: Because Everyone Needs A Buddy', 'the-events-calendar' ); ?></h3>
		<?php
		/**
		 * Filter the "Forums: Because Everyone Needs A Buddy" text on the Settings > Help tab
		 *
		 * @param string $forum_text
		 */
		echo apply_filters( 'tribe_help_tab_forumtext', $forum_text );
		?>

		<h3><?php esc_html_e( 'Not getting help?', 'the-events-calendar' ); ?></h3>
		<?php
		/**
		 * Filter the "Not getting help?" text on the Settings > Help tab
		 *
		 * @param string $outro_text
		 */
		echo apply_filters( 'tribe_help_tab_outro', $outro_text );

		/**
		 * Fires at the end of the help text content on the Settings > Help tab
		 */
		do_action( 'tribe_help_tab_sections' ); ?>

	</div>

</div>


<div id="tribe-help-sidebar">
	<div id="tribe-help-plugin-info">
		<h3><?php esc_html_e( 'The Events Calendar', 'the-events-calendar' ); ?></h3>


		<?php if ( isset( $up_to_date ) ) { ?><p><?php echo $up_to_date; ?></p><?php } ?>
		<?php if ( isset( $version ) ) { ?><p>
			<b><?php esc_html_e( 'Latest Version:', 'the-events-calendar' ); ?></b> <?php echo $version; ?>
			<br /><?php } ?>
			<b><?php esc_html_e( 'Author:', 'the-events-calendar' ); ?></b> <?php esc_html_e( 'Modern Tribe Inc', 'the-events-calendar' ); ?>
			<br />
			<?php
			if ( isset( $requires ) ) {
				?>
				<b><?php esc_html_e( 'Requires:', 'the-events-calendar' ); ?></b> <?php esc_html_e( 'WordPress ', 'the-events-calendar' );
				echo $requires; ?>+<br />
				<?php
			}
			/**
			 * Filter the URL to The Events Calendar plugin page on Wordpress.org
			 *
			 * @param string $url
			 */
			$tribe_help_tab_wp_plugin_url = apply_filters( 'tribe_help_tab_wp_plugin_url', Tribe__Events__Main::WP_PLUGIN_URL );
			?>
			<a href="<?php echo esc_url( $tribe_help_tab_wp_plugin_url ); ?>"><?php esc_html_e( 'Wordpress.org Plugin Page', 'the-events-calendar' ); ?></a>
		</p>
	</div>


	<?php if ( isset( $rating ) && isset( $num_rating ) ) { ?>
		<h3><?php esc_html_e( 'Average Rating', 'the-events-calendar' ); ?></h3>
		<?php wp_star_rating( array(
			'rating' => $rating,
			'type'   => 'percent',
			'number' => $num_rating,
		) ); ?>
		<?php printf( _n( 'Based on %d rating', 'Based on %d ratings', $num_rating, 'the-events-calendar' ), $num_rating ); ?>
		<p>
			<?php
			/**
			 * Filter the URL to The Events Calendar plugin page on Wordpress.org
			 *
			 * @param string $url
			 */
			$tribe_help_tab_wp_plugin_url = apply_filters( 'tribe_help_tab_wp_plugin_url', 'http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5' );
			?>
			<a href="<?php echo esc_url( $tribe_help_tab_wp_plugin_url ); ?>"><?php esc_html_e( 'Give us 5 stars!', 'the-events-calendar' ); ?></a>
		</p>
	<?php } ?>

	<h3><?php esc_html_e( 'Premium Add-Ons', 'the-events-calendar' ); ?></h3>
	<ul>
		<?php foreach ( $premium_add_ons as $addon ) :
			echo '<li>';
			if ( isset( $addon['link'] ) ) {
				echo '<a href="' . esc_url( $addon['link'] ) . '" target="_blank">';
			}
			echo $addon['title'];
			if ( isset( $addon['coming_soon'] ) ) {
				echo is_string( $addon['coming_soon'] ) ? ' ' . $addon['coming_soon'] : ' ' . esc_html__( '(Coming Soon!)', 'the-events-calendar' );
			}
			if ( isset( $addon['link'] ) ) {
				echo '</a>';
			}
			echo '</li>';
		endforeach; ?>
	</ul>


	<h3><?php esc_html_e( 'News and Tutorials', 'the-events-calendar' ); ?></h3>
	<ul>
		<?php
		foreach ( $news_feed as $item ) {
			echo '<li><a href="' . esc_url( $item['link'] . $ga_query_string ) . '">' . $item['title'] . '</a></li>';
		}
		echo '<li><a href="' . esc_url( Tribe__Events__Main::$tecUrl . 'category/products/' . $ga_query_string ) . '">' . $more_text . '</a></li>';
		?>
	</ul>

	<?php
	/**
	 * Fires at the bottom of the sidebar on the Settings > Help tab
	 */
	do_action( 'tribe_help_tab_sidebar' ); ?>

</div>
