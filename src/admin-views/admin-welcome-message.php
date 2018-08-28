<?php

/**
 * The template that displays the welcome message when the plugin is first activated.
 */

$video_url = 'https://vimeo.com/108805711';

?>

<p class="tribe-welcome-message"><?php printf( esc_html__( 'You are running Version %s and deserve a hug :-)', 'the-events-calendar' ), Tribe__Events__Main::VERSION ); ?></p>

<div class="tribe-welcome-video-wrapper">
	<?php echo wp_oembed_get( $video_url ); ?>
</div>

<div class="tribe-row">
	<div class="tribe-half-column">
		<h2><?php esc_html_e( 'We Need Your Help', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( "Your ratings help us bring The Events Calendar to more users. More happy users mean more support, more features, and more of everything you know and love about The Events Calendar. We couldn't do this without your support.", 'the-events-calendar' ); ?></p>
		<p><strong><?php esc_html_e( 'Rate us today!', 'the-events-calendar' ); ?></strong> <a class="tribe-rating-link" href="https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a></p>
		<a href="https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5" target="_blank" class="button-primary"><?php esc_html_e( 'Rate It', 'the-events-calendar' ); ?></a>
	</div>
	<div class="tribe-half-column">
		<h2><?php esc_html_e( 'Newsletter Signup', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Stay in touch with The Events Calendar team. We send out periodic updates, key developer notices, and even the occasional discount.', 'the-events-calendar' ); ?></p>
		<form action="https://moderntribe.createsend.com/t/r/s/athqh/" method="post">
			<p><input id="listthkduyk" name="cm-ol-thkduyk" type="checkbox" /> <label for="listthkduyk"><?php esc_html_e( 'Developer News', 'the-events-calendar' ); ?></label></p>
			<p><input id="listathqh" name="cm-ol-athqh" checked type="checkbox" /> <label for="listathqh"><?php esc_html_e( 'News and Announcements', 'the-events-calendar' ); ?></label></p>
			<p><input id="fieldEmail" class="regular-text" name="cm-athqh-athqh" type="email" placeholder="<?php esc_attr_e( 'Email', 'the-events-calendar' ); ?>" required /></p>
			<button type="submit" class="button-primary"><?php esc_html_e( 'Sign Up', 'the-events-calendar' ); ?></button>
		</form>
	</div>
</div>

<hr/>

<div class="tribe-row tribe-welcome-links">
	<div class="tribe-half-column">
		<h4><?php esc_html_e( 'Getting Started', 'the-events-calendar' ); ?></h4>
		<p><a href="https://m.tri.be/no" target="_blank"><?php esc_html_e( 'Check out the New User Primer &amp; Tutorials', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'Looking for More Features?', 'the-events-calendar' ); ?></h4>
		<p><a href="https://m.tri.be/np" target="_blank"><?php esc_html_e( 'Addons for Community, Tickets, Filters, and more.', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'Support Resources', 'the-events-calendar' ); ?></h4>
		<p><a href="https://m.tri.be/nq" target="_blank"><?php esc_html_e( 'FAQs, Documentation, Tutorials and Forums', 'the-events-calendar' ); ?></a></p>
	</div>
	<div class="tribe-half-column">
		<h4><?php esc_html_e( 'Release Notes', 'the-events-calendar' ); ?></h4>
		<p><a href="https://m.tri.be/1956" target="_blank"><?php esc_html_e( 'Get the Skinny on the Latest Updates', 'the-events-calendar' ); ?></a></p>

		<h4><?php esc_html_e( 'News For Events Users', 'the-events-calendar' ); ?></h4>
		<p><a href="https://m.tri.be/ns" target="_blank"><?php esc_html_e( 'Product Releases, Tutorials and Community Activity', 'the-events-calendar' ); ?></a></p>
	</div>
</div>
