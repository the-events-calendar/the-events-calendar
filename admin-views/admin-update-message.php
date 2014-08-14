<?php

/**
 * The template that displays the update message when the plugin is updated.
 */

?>

<p class="tribe-welcome-message"><?php printf( __( 'You are running Version %s and deserve a hug :-)', 'tribe-events-calendar' ), TribeEvents::VERSION ); ?></p>

<div class="tribe-row">
	<div class="tribe-half-column">
		<strong><?php _e( "IMPORTANT NOTICE", 'tribe-events-calendar' ); ?></strong>
		<p>3.x is a complete overhaul of the plugin, and as a result we're starting the changelog fresh. For release notes from the 2.x lifecycle, see our 2.x release notes.</p>

		<strong>3.6.1</strong>
		<ul>
			<li>Fix minification issues.</li>
			<li>Incorporated updated Greek translation files, courtesy of Yannis Troullinos</li>
			<li>Fixed an issue where the "Hide From Event Listings" checkbox was not hiding events from Month view</li>
		</ul>

		<?php
			/* 

			TODO: Pull in actual changelog (Maybe just previous 2 or 3 versions)
			Should be in the format above

			*/
		?>

	</div>

	<div class="tribe-half-column">
		<h3><?php _e( 'Keep the Core Plugin <strong>FREE</strong>!', 'tribe-events-calendar' ); ?></h3>
		<p><?php _e( 'Every time you rate <strong>5 stars</strong>, a fairy is born. Okay maybe not, but more happy users mean more contributions and help on the forums. The community NEEDS your voice.' ); ?></p>
		<p><a href="http://wordpress.org/support/view/plugin-reviews/the-events-calendar?filter=5" target="_blank" class="button-primary"><?php _e( 'Rate It', 'tribe-events-calendar' ); ?></a></p>

		<br/>
		<h3><?php _e( 'PSST... Want a Discount?', 'tribe-events-calendar' ); ?></h3>		
		<p><?php _e( 'We send out discounts to our core users via our newsletter.' ); ?></p>
		<form action="http://moderntribe.createsend.com/t/r/s/athqh/" method="post">
		    <p>
		        <input id="fieldEmail" size="25" name="cm-athqh-athqh" type="email" placeholder="Email" required />
		    </p>
		    <p>
		        <button type="submit" class="button-primary"><?php _e( 'Sign Up', 'tribe-events-calendar' ); ?></button>
		    </p>
		</form>
		<br/>
		<hr/>

		<div class="tribe-update-links">
			<h4><?php _e( 'Looking for Something Special?', 'tribe-events-calendar' ); ?></h4>
			<p>
				<a href="" target="_blank"><?php _e( 'Pro' ); ?></a><br/>
				<a href="" target="_blank"><?php _e( 'Tickets' ); ?></a><br/>
				<a href="" target="_blank"><?php _e( 'Community Events' ); ?></a><br/>
				<a href="" target="_blank"><?php _e( 'Filters' ); ?></a><br/>
				<a href="" target="_blank"><?php _e( 'Facebook' ); ?></a><br/>
			</p>

			<h4><?php _e( 'News For Events Users', 'tribe-events-calendar' ); ?></h4>

			<p>
				<a href="" target="_blank">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis porttitor massa, non pulvinar ligula. </a>
			</p>

			<?php
				/* 

				TODO: Pull in latest news from the blog (Maybe just previous 2 or 3 posts)
				Should be in the format above

				*/
			?>

		</div>
	</div>
</div>