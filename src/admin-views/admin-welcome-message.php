<?php
/**
 * The template that displays the welcome message when the plugin is first activated.
 */

$main = Tribe__Main::instance();
?>

<div class="tribe-events-admin-content-wrapper">

	<img
		class="tribe-events-admin-graphic tribe-events-admin-graphic--desktop-only"
		src="<?php echo esc_url( tribe_resource_url( 'images/header/welcome-desktop.jpg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'the-events-calendar' ); ?>"
	/>

	<img
		class="tribe-events-admin-graphic tribe-events-admin-graphic--mobile-only"
		src="<?php echo esc_url( tribe_resource_url( 'images/header/welcome-mobile.jpg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'the-events-calendar' ); ?>"
	/>

	<div class="tribe-events-admin-title">
		<img
			class="tribe-events-admin-title__logo"
			src="<?php echo esc_url( tribe_resource_url( 'images/logo/the-events-calendar.svg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'The Events Calendar logo', 'the-events-calendar' ); ?>"
		/>
		<h2 class="tribe-events-admin-title__heading"><?php esc_html_e( 'The Events Calendar', 'the-events-calendar' ); ?></h2>
		<p class="tribe-events-admin-title__description"><?php esc_html_e( 'Thanks for installing The Events Calendar! Here are some handy resources for getting started with our plugins.', 'the-events-calendar' ); ?></p>
	</div>

	<div class="tribe-events-admin-quick-nav">
		<div class="tribe-events-admin-quick-nav__title"><?php esc_html_e( 'Quick Links:', 'the-events-calendar' ); ?></div>
		<ul class="tribe-events-admin-quick-nav__links">
			<li class="tribe-events-admin-quick-nav__link-item"><a href="edit.php?post_type=tribe_events&page=tribe-common" class="tribe-events-admin-quick-nav__link"><?php esc_html_e( 'Configure Settings', 'the-events-calendar' ); ?></a></li>
			<li class="tribe-events-admin-quick-nav__link-item"><a href="post-new.php?post_type=tribe_events" class="tribe-events-admin-quick-nav__link"><?php esc_html_e( 'Create an Event', 'the-events-calendar' ); ?></a></li>
			<li class="tribe-events-admin-quick-nav__link-item"><a href="<?php echo esc_url( Tribe__Events__Main::instance()->getLink() ); ?>" class="tribe-events-admin-quick-nav__link"><?php esc_html_e( 'View My Calendar', 'the-events-calendar' ); ?></a></li>
		</ul>
	</div>

	<h3 class="tribe-events-admin-section-header"><?php esc_html_e( 'Helpful Resources', 'the-events-calendar' ); ?></h3>

	<div class="tribe-events-admin-card-grid">
		<div class="tribe-events-admin-card tribe-events-admin-card--3up tribe-events-admin-card--first">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/guide-book.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'illustration of a book with The Events Calendar logo', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Getting Started Guide', 'the-events-calendar' ); ?></div>
			<div class="tribe-events-admin-card__description"><?php esc_html_e( 'New to The Events Calendar? Here\'s everything you need to get started.', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="https://evnt.is/welcom"><?php esc_html_e( 'Check out the guide', 'the-events-calendar' ); ?></a>
		</div>
		<div class="tribe-events-admin-card tribe-events-admin-card--3up tribe-events-admin-card--middle">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/knowledgebase.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'illustration of a thought lightbulb coming from a book', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Knowledgebase', 'the-events-calendar' ); ?></div>
			<div class="tribe-events-admin-card__description"><?php esc_html_e( 'Ready to dig deeper? Our Knowledgebase can help you get the most out of The Events Calendar suite.', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="https://evnt.is/kb-welcome"><?php esc_html_e( 'Dig deeper', 'the-events-calendar' ); ?></a>
		</div>
		<div class="tribe-events-admin-card tribe-events-admin-card--3up tribe-events-admin-card--last">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/translations.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'illustration of characters being translated', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Translations', 'the-events-calendar' ); ?></div>
			<div class="tribe-events-admin-card__description"><?php esc_html_e( 'Need a language other than English? We\'ve got you covered here.', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="https://evnt.is/language"><?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?></a>
		</div>

		<div class="tribe-events-admin-card tribe-events-admin-card--1up">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/virtual-events.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'illustration of a phone screen with a person\'s face', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Virtual Event Resources', 'the-events-calendar' ); ?></div>
			<div class="tribe-events-admin-card__description"><?php esc_html_e( 'Tips and tools to help with planning online events, webinars, and more on WordPress and beyond.', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="https://evnt.is/1ame"><?php esc_html_e( 'Get started with online events', 'the-events-calendar' ); ?></a>
		</div>

		<div class="tribe-events-admin-card tribe-events-admin-card--2up tribe-events-admin-card--first">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/migration.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'illustration of an event moving from one calendar to another', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Migrating events from another calendar?', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="https://evnt.is/1amf"><?php esc_html_e( 'We can help with that', 'the-events-calendar' ); ?></a>
		</div>
		<div class="tribe-events-admin-card tribe-events-admin-card--2up tribe-events-admin-card--second">
			<img
				class="tribe-events-admin-card__image"
				src="<?php echo esc_url( tribe_resource_url( 'images/welcome/next-level.jpg', false, null, $main ) ); ?>"
				alt="<?php esc_attr_e( 'book with The Events Calendar logo', 'the-events-calendar' ); ?>"
			/>
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Want to take your events to the next level?', 'the-events-calendar' ); ?></div>
			<a class="tribe-events-admin-card__link" href="edit.php?post_type=tribe_events&page=tribe-app-shop"><?php esc_html_e( 'Check out our suite of add-ons', 'the-events-calendar' ); ?></a>
		</div>

		<div class="tribe-events-admin-card tribe-events-admin-card--1up tribe-events-admin-card--promo-blue">
			<div class="tribe-events-admin-card__title"><?php esc_html_e( 'Want this emailed to you?', 'the-events-calendar' ); ?></div>
			<div class="tribe-events-admin-card__description"><?php esc_html_e( 'Keep this list of links on hand and stay subscribed to receive tips and tricks about The Events Calendar products.', 'the-events-calendar' ); ?></div>

			<form class="tribe-events-admin-card__form" action="https://support-api.tri.be/mailing-list/subscribe" method="post">
				<input class="tribe-events-admin-card__input" name="email" type="email" placeholder="<?php esc_attr_e( 'Your email', 'the-events-calendar' ); ?>" required />

				<button class="tribe-events-admin-card__button" type="submit"><?php esc_html_e( 'Sign Up', 'the-events-calendar' ); ?></button>

				<input type="hidden" name="list" value="tec-newsletter" />
				<input type="hidden" name="source" value="plugin:tec" />
				<input type="hidden" name="consent" value="checked" />
			</form>
		</div>
	</div>

	<img
		class="tribe-events-admin-footer-logo"
		src="<?php echo esc_url( tribe_resource_url( 'images/logo/tec-brand.svg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar brand logo', 'the-events-calendar' ); ?>"
	/>

</div>