<?php
/**
 * The template that displays the welcome message when the plugin is first activated.
 */
?>

<p class="tribe-welcome-version"><?php printf( '<strong>%1$s %2$s</strong>', esc_html__( 'Version', 'the-events-calendar' ), Tribe__Events__Main::VERSION ); ?></p>

<p class="tribe-welcome-message"><?php esc_html_e( 'The Events Calendar provides the tools you need—like customizable templates, widgets, views, and more—to make sharing your events online a breeze.', 'the-events-calendar' ); ?></p>

<p class="tribe-welcome-message"><?php esc_html_e( 'Check out the resources below for a comprehensive introduction to your new plugin. With just a few quick clicks, you’ll be sharing and promoting your events in no time!', 'the-events-calendar' ); ?></p>

<div class="tribe-row">

	<div class="tribe-half-column">
		<h2 data-tribe-icon="dashicons-welcome-learn-more"><?php esc_html_e( 'Getting Started', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Start strong with these helpful resources.', 'the-events-calendar' ); ?></p>
		<ul>
			<li><a href="http://m.tri.be/1aa3" target="_blank"><?php esc_html_e( 'New User Primer', 'the-events-calendar' ); ?></a></li>
			<li><a href="http://m.tri.be/1aa4" target="_blank"><?php esc_html_e( 'Settings Overview', 'the-events-calendar' ); ?></a></li>
			<li><a href="http://m.tri.be/1aa5" target="_blank"><?php esc_html_e( 'Themer\'s Guide', 'the-events-calendar' ); ?></a></li>
		</ul>
	</div>

	<div class="tribe-half-column">
		<h2 data-tribe-icon="dashicons-sos"><?php esc_html_e( 'Resources and Support', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'We’ve got your back every step of the way.', 'the-events-calendar' ); ?></p>
		<ul>
			<li><a href="http://m.tri.be/1aa6" target="_blank"><?php esc_html_e( 'Search the Knowledgebase', 'the-events-calendar' ); ?></a></li>
			<li><a href="http://m.tri.be/1aa7" target="_blank"><?php esc_html_e( 'Available Translations', 'the-events-calendar' ); ?></a></li>
			<li><a href="http://m.tri.be/1aa8" target="_blank"><?php esc_html_e( 'Submit a Help Desk Request', 'the-events-calendar' ); ?></a></li>
		</ul>
	</div>
</div>

<div class="tribe-row">

	<div class="tribe-half-column">
		<h2><?php esc_html_e( 'The Latest and Greatest', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Frequent maintenance releases keep your ticket sales running smoothly.', 'the-events-calendar' ); ?> <a href="http://m.tri.be/1aa9" target="_blank"><?php esc_html_e( 'View the latest changelog', 'the-events-calendar' ); ?></a>.</p>
		<p><?php esc_html_e( 'Gearing up with Gutenberg?', 'the-events-calendar' ); ?> <a href="http://m.tri.be/1aaa" target="_blank"><?php esc_html_e( 'Get the latest block editor news', 'the-events-calendar' ); ?></a>.</p>
	</div>

	<div class="tribe-half-column">
		<h2 data-tribe-icon="dashicons-megaphone"><?php esc_html_e( 'Don\'t Miss Out', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Get the latest on The Events Calendar, occasional discounts, and hilarious gifs delivered straight to your inbox.', 'the-events-calendar' ); ?></p>
		<form id="subForm" class="js-cm-form" action="https://www.createsend.com/t/subscribeerror?description=" method="post" data-id="5B5E7037DA78A748374AD499497E309E4B5B9EDD1E951EF147AAADB4A3E12D9E9787C0F45F75858066CA86E8304E95F49CDB57115BC93CCF66450D4FDD3CEF5B">
		<p>
		    <input id="fieldEmail" name="cm-athqh-athqh" placeholder="<?php esc_attr_e( 'Email', 'the-events-calendar' ); ?>" type="email" class="js-cm-email-input medium-text" required />
		</p>
		<div>
		    <input id="cm-privacy-consent" name="cm-privacy-consent" required type="checkbox" role="checkbox" aria-checked="false" />
		    <label for="cm-privacy-consent"><?php esc_html_e( 'Add me to the list', 'the-events-calendar' ); ?></label>
		    <input id="cm-privacy-consent-hidden" name="cm-privacy-consent-hidden" type="hidden" value="true" />
		</div>
		<p>
		    <button class="js-cm-submit-button button button-primary" type="submit"><?php esc_html_e( 'Subscribe', 'the-events-calendar' ); ?></button>
		</p>
		</form>
		<script type="text/javascript" src="https://js.createsend1.com/javascript/copypastesubscribeformlogic.js"></script>
	</div>
</div>

<div class="tribe-row">
	<div class="tribe-half-column">
		<h2 data-tribe-icon="dashicons-heart"><?php esc_html_e( 'We Need Your Help', 'the-events-calendar' ); ?></h2>
		<p><?php esc_html_e( 'Your ratings keep us focused on making our plugins as useful as possible so we can help other WordPress users just like you.', 'the-events-calendar' ); ?></p>
		<p><strong><?php esc_html_e( 'Rate us today!', 'the-events-calendar' ); ?></strong> <a class="tribe-rating-link" href="https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a></p>
		<a href="https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5" target="_blank" class="button-primary"><?php esc_html_e( 'Rate It', 'the-events-calendar' ); ?></a>
	</div>

	<div class="tribe-half-column"></div>
</div>