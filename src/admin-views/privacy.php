<?php
/**
 * The Events Calendar Privacy
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tribe-events-privacy">
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Hello,', 'the-events-calendar' ); ?></p>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'This information serves as a guide on what sections need to be modified due to usage of The Events Calendar and its Add-ons.', 'the-events-calendar' ); ?></p>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'You should include the information below in the correct sections of you privacy policy.', 'the-events-calendar' ); ?></p>
	<p class="privacy-policy-tutorial"><strong> <?php esc_html_e( 'Disclaimer:', 'the-events-calendar' ); ?></strong> <?php esc_html_e( 'This information is only for guidance and not to be considered as legal advice.', 'the-events-calendar' ); ?></p>

	<h2><?php esc_html_e( 'What personal data we collect and why we collect it', 'the-events-calendar' ); ?></h2>

	<h3><?php esc_html_e( 'Event, Venue, and Organizer Information', 'the-events-calendar' ); ?></h3>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Through the usage of The Events Calendar, Events Calendar PRO, The Events Calendar Filter Bar, Eventbrite Tickets, and Community Events plugins, as well as our Event Aggregator Import service (contained within The Events Calendar plugin), information may be collected and stored within your website’s database.', 'the-events-calendar' ); ?></p>
	<p class="privacy-policy-tutorial"><strong><?php esc_html_e( 'Suggested text:', 'event-tickets' ); ?></strong></p>
	<p><?php esc_html_e( 'If you create, submit, import, save, or publish Event, Venue, or Organizer information, such information is retained in the local database:', 'the-events-calendar' ); ?></p>

	<ol>
		<li><?php esc_html_e( 'Venue information: name, address, city, country, state, postal code, phone, website, geographical coordinates (latitude and longitude)', 'the-events-calendar' ); ?></li>
		<li><?php esc_html_e( 'Organizer information: name, phone, website, email', 'the-events-calendar' ); ?></li>
		<li><?php esc_html_e( 'Event information: website, cost, description, date, time, image', 'the-events-calendar' ); ?></li>
	</ol>

	<h4><?php esc_html_e( 'Importing Events, Venues, and Organizers:', 'the-events-calendar' ); ?></h4>

	<ol>
		<li><?php esc_html_e( 'All data present within a CSV or ICS file and external URLs (for events, venues, organizers, and tickets)', 'the-events-calendar' ); ?></li>
		<li><?php esc_html_e( 'Import origin data (URL from where events are being imported—such as Eventbrite, MeetUp, other compatible URL sources, and more, which can include similar or same data as listed above)', 'the-events-calendar' ); ?></li>
		<li><?php esc_html_e( 'Eventbrite Ticket information: name, description, cost, type, quantity', 'the-events-calendar' ); ?></li>
	</ol>

	<p><?php esc_html_e( 'Please note that to create new events through the Community Events submission form, a user must hold a website account on this domain. This information is retained in the local database. It is also possible to create events anonymously, if the site owner has this option enabled.', 'the-events-calendar' ); ?></p>

	<p><?php esc_html_e( 'When purchasing Eventbrite Tickets, attendee, purchaser, and order information are stored and managed by Eventbrite.', 'the-events-calendar' ); ?></p>

	<h3><?php esc_html_e( 'API Keys', 'the-events-calendar' ); ?></h3>

	<p class="privacy-policy-tutorial"><?php esc_html_e( 'The Events Calendar suite offers the use of third-party API keys. The primary functions are to enhance the features we\'ve built in, some of which use Google Maps, PayPal, Eventbrite, and Meetup. These API keys are not supplied by Modern Tribe.', 'the-events-calendar' ); ?></p>

	<p class="privacy-policy-tutorial"><strong><?php esc_html_e( 'Suggested text:', 'event-tickets' ); ?></strong></p>

	<p><?php esc_html_e( 'We make use of certain API keys, in order to provide specific features.', 'the-events-calendar' ); ?></p>

	<p><?php esc_html_e( 'These API keys may include the following third party services: Google Maps, Meetup, PayPal, and Eventbrite (API key, auth URL and Client Secret).', 'the-events-calendar' ); ?></p>

	<h3 class="privacy-policy-tutorial"><?php esc_html_e( 'How Long You Retain this Data', 'the-events-calendar' ); ?></h3>

	<p class="privacy-policy-tutorial"><?php esc_html_e( 'All information (data) is retained in the local database indefinitely, unless otherwise deleted.', 'the-events-calendar' ); ?></p>

	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Certain data may be exported or removed upon users’ requests via the existing Exporter or Eraser. Please note, however, that several “edge cases” exist in which we are unable to perfect the gathering and export of all data for your end users. We suggest running a search in your local database, as well as within the WordPress Dashboard, in order to identify all data collected and stored for your specific user requests.', 'the-events-calendar' ); ?></p>

	<h3 class="privacy-policy-tutorial"><?php esc_html_e( 'Where We Send Your Data', 'the-events-calendar' ); ?></h3>

	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Modern Tribe does not send any user data outside of your website by default.', 'the-events-calendar' ); ?></p>

	<p class="privacy-policy-tutorial"><?php esc_html_e( 'If you have extended our plugin(s) to send data to a third-party service such as Eventbrite, Google Maps, or PayPal, user information may be passed to these external services. These services may be located abroad.', 'the-events-calendar' ); ?></p>

</div>