<div style="float: right; width: 200px; margin: 15px;">
<div style="border: 1px solid #CCC; padding: 0 12px;">
<h3><?php _e('The Events Calendar'); ?></h3>
<?php 
$tec_info = file_get_contents( 'http://wpapi.org/api/plugin/the-events-calendar.php' );
$tec_info = unserialize($tec_info);
$rating = $tec_info['rating'] / 20;
?>
<p><b><?php _e('Latest Version:', 'tribe-events-calendar'); ?></b> <?php echo( $tec_info['version'] ); ?><br />
<b><?php _e('Author:', 'tribe-events-calendar'); ?></b> <?php echo( $tec_info['author']['name'] ); ?><br />
<b><?php _e('Requires:', 'tribe-events-calendar'); ?></b> <?php _e('Wordpress ', 'tribe-events-calendar'); echo( $tec_info['requires'] ); ?><br /> 
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
<h3><?php _e('Free Add-Ons', 'tribe-events-calendar'); ?></h3>
<ul>
<li><a href="http://wordpress.org/extend/plugins/advanced-post-manager/?ref=tec-readme">Advanced Post Manager</a></li>
<li>Event Importer (coming soon!)</li>
<li>Facebook Sync Events (coming soon!)</li>
</ul>
<h3><?php _e('Premium Add-Ons', 'tribe-events-calendar'); ?></h3>
<ul>
<li><a href="http://tri.be/wordpress-events-calendar-pro/?ref=tec-readme">Events Calendar Pro</a></li>
<li><a href="http://tri.be/shop/wordpress-eventbrite-tickets/?ref=tec-readme">Eventbrite Tickets (coming soon!)</a></li>
<li><a href="http://tri.be/shop/wordpress-community-events/?ref=tec-readme">Community Events (coming soon!)</a></li>
<li><a href="http://tri.be/shop/conference-manager/?ref=tec-readme">Conference Manager (coming later in 2012)</a></li>
<li><a href="http://tri.be/shop/wootickets/?ref=tec-readme">WooTickets (coming later in 2012)</a></li>
</ul>
<h3><?php _e('News and Tutorials', 'tribe-events-calendar'); ?></h3>
<ul>
<li><a href="#">News Article 1</a></li>
<li><a href="#">News Article 2</a></li>
<li><a href="#">More...</a></li>
</ul>
</div>
<p><?php _e('Hi! Thank you so much for using the labor of our love. We are Modern Tribe and we are here to help you kick ass.', 'tribe-events-calendar'); ?></p>
<h3><?php _e('Getting Started', 'tribe-events-calendar'); ?></h3>
<p class="admin-indent"><?php _e('This is where information and help for new users will be. Rob is working on drafting all sorts of useful information for The Events Calendar first time users. Yay!!!', 'tribe-events-calendar'); ?></p>
<h3><?php _e('Resources to Help You Kick Ass', 'tribe-events-calendar'); ?></h3>
<ul class="admin-indent">
<li><a href="http://tri.be/support/documentation/"><?php _e('Documentation', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/support/faqs/"><?php _e('FAQ', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/help-video/"><?php _e('Help Videos', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/tutorial/"><?php _e('Tutorials', 'tribe-events-calendar'); ?></a></li>
<li><a href="http://tri.be/category/products/release-notes/"><?php _e('Release Notes', 'tribe-events-calendar'); ?></a></li>
</ul>
<h3><?php _e('Everyone Needs a Buddy', 'tribe-events-calendar'); ?></h3>
<p class="admin-indent"><?php _e('Good thing about being a PRO user is that you are not alone. Take advantage of our awesome community and smart, friendly support team.', 'tribe-events-calendar'); ?></p>
<p class="admin-indent"><?php _e('How to submit your issue to the support forum:', 'tribe-events-calendar'); ?></p>
<p class="admin-indent"><a href="http://tri.be/support/forums/"><?php _e('Modern Tribe Support Forum', 'tribe-events-calendar'); ?></p>