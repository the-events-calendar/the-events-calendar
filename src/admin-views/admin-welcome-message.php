<?php

/**
 * The template that displays the welcome message when the plugin is first activated.
 */

$video_url = 'http://vimeo.com/108805711';

?>

<p class="tribe-welcome-message"><?php echo esc_html( sprintf( __( 'You are running Version %s and deserve a hug :-)', 'the-events-calendar' ), Tribe__Events__Main::VERSION ) ); ?></p>

<div class="tribe-welcome-video-wrapper">
	<?php echo wp_oembed_get( $video_url ); ?>
</div>

<div class="tribe-row">
	<div class="tribe-half-column">
		<h2><?php _e( 'Keep The Events Calendar Core FREE', 'the-events-calendar' ); ?></h2>
		<p><?php _e( "5 star ratings help us bring TEC to more users. More happy users mean more support, more features, and more of everything you know and love about The Events Calendar. We couldn't do this without your support.", 'the-events-calendar' ); ?></p>
		<p><strong><?php esc_html_e( 'Rate it five stars today!', 'the-events-calendar' ); ?></strong> <a class="tribe-rating-link" href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a></p>
		<a href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank" class="button-primary"><?php esc_html_e( 'Rate It', 'the-events-calendar' ); ?></a>
	</div>
	<div class="tribe-half-column">
		<h2><?php esc_html_e( 'Newsletter Signup', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Stay in touch with The Events Calendar Pro. We send out periodic updates, key developer notices, and even the occasional discount.', 'the-events-calendar' ); ?></p>
		<form action="http://moderntribe.createsend.com/t/r/s/athqh/" method="post">
			<p><input id="listthkduyk" name="cm-ol-thkduyk" type="checkbox" /> <label for="listthkduyk">Developer News</label></p>
			<p><input id="listathqh" name="cm-ol-athqh" checked type="checkbox" /> <label for="listathqh">News and Announcements</label></p>
			<p><input id="fieldEmail" class="regular-text" name="cm-athqh-athqh" type="email" placeholder="Email" required /></p>
			<button type="submit" class="button-primary"><?php esc_html_e( 'Sign Up', 'the-events-calendar' ); ?></button>
		</form>
	</div>
</div>

<hr/>

<div class="tribe-row tribe-welcome-links">
	<div class="tribe-half-column">
		<h4><?php esc_html_e( 'Getting Started', 'the-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/no" target="_blank"><?php esc_html_e( 'Check out the New User Primer &amp; Tutorials', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'Looking for More Features?', 'the-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/np" target="_blank"><?php esc_html_e( 'Addons for Community, Tickets, Filters, Facebook and more.', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'Support Resources', 'the-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/nq" target="_blank"><?php esc_html_e( 'FAQs, Documentation, Tutorials and Forums', 'the-events-calendar' ); ?></a></p>
	</div>
	<div class="tribe-half-column">
		<h4><?php esc_html_e( 'Release Notes', 'the-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/nr" target="_blank"><?php esc_html_e( 'Get the Skinny on the Latest Updates', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'News For Events Users', 'the-events-calendar' ); ?></h4>
		<p><a href="http://m.tri.be/ns" target="_blank"><?php esc_html_e( 'Product Releases, Tutorials and Community Activity', 'the-events-calendar' ); ?></a></p>
	</div>
</div>
