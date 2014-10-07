<?php

/**
 * The template that displays the update message when the plugin is updated.
 */

?>

<p class="tribe-welcome-message"><?php printf( __( 'You are running Version %s and deserve a hug :-)', 'tribe-events-calendar' ), TribeEvents::VERSION ); ?></p>

<div class="tribe-row">
	<div class="tribe-half-column">
		<?php

		$changelog = new Tribe__Events__Changelog_Reader();
		foreach ( $changelog->get_changelog() as $section => $messages ):
			if ( empty($messages) ) {
				continue;
			}
			?><strong><?php esc_html_e($section); ?></strong>
			<ul>
				<?php foreach ( $messages as $m ): ?>
				<li><?php echo $m; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php
		endforeach; ?>

	</div>

	<div class="tribe-half-column">
		<h3><?php _e( 'Keep the Core Plugin <strong>FREE</strong>!', 'tribe-events-calendar' ); ?></h3>
		<p><?php _e( 'Every time you rate <strong>5 stars</strong>, a fairy is born. Okay maybe not, but more happy users mean more contributions and help on the forums. The community NEEDS your voice.' ); ?></p>
		<p><a href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank" class="button-primary"><?php _e( 'Rate It', 'tribe-events-calendar' ); ?></a></p>

		<br/>
		<h3><?php _e( 'PSST... Want a Discount?', 'tribe-events-calendar' ); ?></h3>		
		<p><?php _e( 'We send out discounts to our core users via our newsletter.' ); ?></p>
		<form action="http://moderntribe.createsend.com/t/r/s/athqh/" method="post">
			<p><input id="listthkduyk" name="cm-ol-thkduyk" type="checkbox" /> <label for="listthkduyk">Developer News</label></p>
			<p><input id="listathqh" name="cm-ol-athqh" checked type="checkbox" /> <label for="listathqh">News and Announcements</label></p>
			<p><input id="fieldEmail" class="regular-text" name="cm-athqh-athqh" type="email" placeholder="Email" required /></p>
			<button type="submit" class="button-primary"><?php _e( 'Sign Up', 'tribe-events-calendar' ); ?></button>
		</form>
		<br/>
		<hr/>

		<div class="tribe-update-links">
			<h4><?php _e( 'Looking for Something Special?', 'tribe-events-calendar' ); ?></h4>
			<p>
				<a href="http://m.tri.be/nt" target="_blank"><?php _e( 'Pro' ); ?></a><br/>
				<a href="http://m.tri.be/nu" target="_blank"><?php _e( 'Tickets' ); ?></a><br/>
				<a href="http://m.tri.be/nx" target="_blank"><?php _e( 'Community Events' ); ?></a><br/>
				<a href="http://m.tri.be/nv" target="_blank"><?php _e( 'Filters' ); ?></a><br/>
				<a href="http://m.tri.be/nw" target="_blank"><?php _e( 'Facebook' ); ?></a><br/><br/>
			</p>

			<h4><?php _e( 'News For Events Users', 'tribe-events-calendar' ); ?></h4>

			<?php TribeEvents::instance()->outputDashboardWidget(3); ?>

		</div>
	</div>
</div>