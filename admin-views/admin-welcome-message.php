<?php

/**
 * The template that displays the welcome message when the plugin is first activated.
 */

$video_url = 'https://www.youtube.com/watch?v=HHohYGTk3QQ';

?>

<p class="tribe-welcome-message"><?php printf( __( 'You are running Version %s and deserve a hug :-)', 'tribe-events-calendar' ), TribeEvents::VERSION ); ?></p>

<div class="tribe-welcome-video-wrapper">
	<?php echo wp_oembed_get( $video_url ); ?>
</div>

<div class="tribe-row">
	<div class="tribe-half-column">
		<h3><?php _e( 'Keep The Events Calendar Core FREE', 'tribe-events-calendar' ); ?></h3>
		<p><?php _e( "5 star ratings help us bring TEC to more users. More happy users mean more support, more features, and more of everything you know and love about The Events Calendar. We couldn't do this without your support.", 'tribe-events-calendar' ); ?></p>
		<p><strong><?php _e( 'Rate it five stars today!', 'tribe-events-calendar' ); ?></strong> <a class="tribe-rating-link" href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a></p>
		<a href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank" class="button-primary"><?php _e( 'Rate It', 'tribe-events-calendar' ); ?></a>
	</div>
	<div class="tribe-half-column">
		<h3><?php _e( "Newsletter Signup", 'tribe-events-calendar' ); ?></h3>
		<p><?php _e( "Stay in touch with The Events Calendar Pro. We send out the occiasional update, key developer notices, and even the occasional discount.", 'tribe-events-calendar' ); ?></p>
		<form action="http://moderntribe.createsend.com/t/r/s/athqh/" method="post">
			<p><input id="listthkduyk" name="cm-ol-thkduyk" type="checkbox" /> <label for="listthkduyk">Developer News</label></p>
			<p><input id="fieldEmail" class="regular-text" name="cm-athqh-athqh" type="email" placeholder="Email" required /></p>
			<button type="submit" class="button-primary"><?php _e( 'Sign Up', 'tribe-events-calendar' ); ?></button>
		</form>
	</div>
</div>

<hr/>

<div class="tribe-row tribe-welcome-links">
	<div class="tribe-half-column">
		<h4><?php _e( 'Getting Started', 'tribe-events-calendar' ); ?></h4>		
		<p><a href="http://m.tri.be/no" target="_blank"><?php _e( 'Check out the New User Primer &amp; Tutorials', 'tribe-events-calendar' ); ?></a></p>
		
		<h4><?php _e( 'Looking for More Features?', 'tribe-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/np" target="_blank"><?php _e( 'Addons for Community, Tickets, Filters, Facebook and more.', 'tribe-events-calendar' ); ?></a></p>

		<h4><?php _e( 'Support Resources', 'tribe-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/nq" target="_blank"><?php _e( 'FAQs, Documentation, Tutorials and Forums', 'tribe-events-calendar' ); ?></a></p>		
	</div>
	<div class="tribe-half-column">
		<h4><?php _e( 'Release Notes', 'tribe-events-calendar' ); ?></h4>		
		<p><a href="http://m.tri.be/nr" target="_blank"><?php _e( 'Get the Skinny on the Latest Updates', 'tribe-events-calendar' ); ?></a></p>
		
		<h4><?php _e( 'News For Events Users', 'tribe-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/ns" target="_blank"><?php _e( 'Product Releases, Tutorials and Community Activity', 'tribe-events-calendar' ); ?></a></p>
	</div>
</div>