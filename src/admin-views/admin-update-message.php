<?php

/**
 * The template that displays the update message when the plugin is updated.
 */

?>

<p class="tribe-welcome-message"><?php echo esc_html( sprintf( __( 'You are running Version %s and deserve a hug :-)', 'the-events-calendar' ), Tribe__Events__Main::VERSION ) ); ?></p>

<div class="tribe-row">
	<div class="tribe-half-column">
		<?php

		$changelog = new Tribe__Changelog_Reader();
		foreach ( $changelog->get_changelog() as $section => $messages ):
			if ( empty( $messages ) ) {
				continue;
			}
			?><strong><?php esc_html_e( $section ); ?></strong>
			<ul>
				<?php foreach ( $messages as $m ): ?>
				<li><?php echo $m; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php
		endforeach; ?>

	</div>

	<div class="tribe-half-column">
		<h2><?php printf( esc_html__( 'Keep the Core Plugin %sFREE%s!', 'the-events-calendar' ), '<strong>', '</strong>' ); ?></h2>
		<p><?php printf( esc_html__( 'Every time you rate %s5 stars%s, a fairy is born. Okay maybe not, but more happy users mean more contributions and help on the forums. The community NEEDS your voice.', 'the-events-calendar' ), '<strong>', '</strong>' ); ?></p>
		<p><a href="https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5" target="_blank" class="button-primary"><?php esc_html_e( 'Rate It', 'the-events-calendar' ); ?></a></p>

		<br/>
		<h2><?php esc_html_e( 'PSST... Want a Discount?', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'We send out discounts to our core users via our newsletter.', 'the-events-calendar' ); ?></p>
		<form action="https://moderntribe.createsend.com/t/r/s/athqh/" method="post">
			<p><input id="listthkduyk" name="cm-ol-thkduyk" type="checkbox" /> <label for="listthkduyk">Developer News</label></p>
			<p><input id="listathqh" name="cm-ol-athqh" checked type="checkbox" /> <label for="listathqh">News and Announcements</label></p>
			<p><input id="fieldEmail" class="regular-text" name="cm-athqh-athqh" type="email" placeholder="Email" required /></p>
			<button type="submit" class="button-primary"><?php esc_html_e( 'Sign Up', 'the-events-calendar' ); ?></button>
		</form>
		<br/>
		<hr/>

		<div class="tribe-update-links">
			<h4><?php esc_html_e( 'Looking for Something Special?', 'the-events-calendar' ); ?></h4>
			<p>
				<a href="https://m.tri.be/nt" target="_blank"><?php esc_html_e( 'Pro', 'the-events-calendar' ); ?></a><br/>
				<a href="https://m.tri.be/nu" target="_blank"><?php esc_html_e( 'Tickets', 'the-events-calendar' ); ?></a><br/>
				<a href="https://m.tri.be/nx" target="_blank"><?php esc_html_e( 'Community Events', 'the-events-calendar' ); ?></a><br/>
				<a href="https://m.tri.be/nv" target="_blank"><?php esc_html_e( 'Filters', 'the-events-calendar' ); ?></a><br/>
				<a href="https://m.tri.be/19mo" target="_blank"><?php esc_html_e( 'Imports', 'the-events-calendar' ); ?></a><br/><br/>
			</p>

			<h4><?php esc_html_e( 'News For Events Users', 'the-events-calendar' ); ?></h4>

			<?php Tribe__Events__Main::instance()->outputDashboardWidget( 3 ); ?>

		</div>
	</div>
</div>
