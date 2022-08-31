<?php
/**
 * The template that displays the welcome message when the plugin is first activated.
 */

use Tribe__Events__Main as TEC;

$common_main = Tribe__Main::instance();
$notices = Tribe__Admin__Notices::instance();
$tec = TEC::instance();
$settings_url = tribe( Tribe\Events\Admin\Settings::class )->get_url();
?>

<div class="tribe-events-admin-content-wrapper">
	<img
		class="tribe-events-admin-graphic tribe-events-admin-graphic--desktop-only"
		src="<?php echo esc_url( tribe_resource_url( 'update-page-graphic.png', false, 'src/resources/images/', $tec ) ); ?>"
		alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'the-events-calendar' ); ?>"
	/>

	<div class="tribe-events-admin-title">
		<img
		class="tribe-events-admin-title__logo"
		src="<?php echo esc_url( tribe_resource_url( 'images/logo/the-events-calendar.svg', false, null, $common_main ) ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar logo', 'the-events-calendar' ); ?>"
		/>

		<div class="tribe-events-admin-title__heading-wrapper">
			<p class="tribe-events-admin-title__sub-heading"><?php esc_html_e( 'Welcome to', 'the-events-calendar' ); ?></p>
			<h2 class="tribe-events-admin-title__heading"><?php esc_html_e( 'The Events Calendar 6.0', 'the-events-calendar' ); ?></h2>
			<a href="<?php echo esc_url( $settings_url ) ?>" class="tribe-events-admin-title__link"><?php esc_html_e( 'Take me to Settings ›', 'the-events-calendar' ); ?></a>
		</div>
	</div>

	<?php
		echo $notices->render( 'events-legacy-views-updated', tribe( Tribe\Events\Admin\Notice\Legacy_Views_Updated::class )->notice() );
		echo $notices->render( 'event-update-6-0', tribe( 'tec.admin.notice.update' )->notice() );
	?>

	<div class="tec-update-page-block">
		<h3><?php esc_html_e( "What’s New in The Events Calendar 6.0", 'the-events-calendar' ); ?></h3>
		<div class="tec-update-page-grid">
			<div class="tec-update-page-grid__item">
				<iframe src="https://evnt.is/1b8k" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="width:100%;height:280px;" title="<?php esc_attr_e( 'Video describing features on The Events Calendar 6.0', 'the-events-calendar' ); ?>"></iframe>
				<script src="https://player.vimeo.com/api/player.js"></script>
			</div>

			<div class="tec-update-page-grid__item">
				<p>
					<?php esc_html_e( "We’re excited to bring all of our users faster event creation and editing in the WordPress dashboard as well as quicker loading times for your website’s calendar." ); ?>
					<?php printf( wp_kses( __( 'Watch the video or check out the <a href="%s">release notes</a> to learn more.', 'the-events-calendar' ), [ 'a' => [ 'href' => [] ] ] ), esc_url( 'https://theeventscalendar.com/category/release-notes/' ) ); ?>
				</p>
				<p><?php printf( wp_kses( __( 'Be sure to <a href="%s">migrate</a> to the new data storage system so you can take advantage of the improved performance.', 'the-events-calendar' ), [ 'a' => [ 'href' => [] ] ] ), esc_url( 'https://evnt.is/1b79' ) ); ?></p>
			</div>
	</div>

	<div class="tec-update-page-block">
		<h3>
			<img class="tec-inline-icon" src="<?php echo esc_url( tribe_resource_url( 'bolt.svg', false, 'src/resources/images/icons/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Lightening bolt icon', 'the-events-calendar' ); ?>" />
			<?php esc_html_e( 'New Features for Events Calendar Pro', 'the-events-calendar' ); ?>
		</h3>
		<div class="tec-update-page-grid">
			<div class="tec-update-page-grid__item">
				<p><?php esc_html_e( 'A game-changing ✨new✨ post type, Series, allows you to group and display any single or recurring events together like never before.', 'the-events-calendar' ); ?></p>
				<p><?php esc_html_e( 'With Series, you can edit an individual occurrence of a recurring event without disconnecting it from the others, allowing you to build complex event Series with different venues, images, and more.', 'the-events-calendar' ); ?></p>
				<p class="tec-update-page__row"><a href="https://evnt.is/1b94"><?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?></a> <a href="https://evnt.is/1b95"><?php esc_html_e( 'Watch a video', 'the-events-calendar' ); ?></a> <a href="https://evnt.is/1b96"><?php esc_html_e( 'See a demo', 'the-events-calendar' ); ?></a></p>
			</div>

			<div class="tec-update-page-grid__item">
				<img src="<?php echo esc_url( tribe_resource_url( 'update-page-features-1.png', false, 'src/resources/images/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Event series graphic', 'the-events-calendar' ); ?>" />
			</div>
		</div>
		<div class="tec-update-page-grid">
			<div class="tec-update-page-grid__item has-media">
				<img src="<?php echo esc_url( tribe_resource_url( 'update-page-features-2.png', false, 'src/resources/images/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Event series graphic', 'the-events-calendar' ); ?>" />
				<p><b><?php esc_html_e( 'Advanced editing options', 'the-events-calendar' ); ?></b> <?php esc_html_e( 'for recurring events give you more possibilities for complex event management.', 'the-events-calendar' ); ?></p>
			</div>
			<div class="tec-update-page-grid__item has-media">
				<img src="<?php echo esc_url( tribe_resource_url( 'update-page-features-3.png', false, 'src/resources/images/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Event series graphic', 'the-events-calendar' ); ?>" />
				<p><b><?php esc_html_e( 'With the Duplicate Event Tool', 'the-events-calendar' ); ?></b> <?php esc_html_e( 'you can replicate and modify any events in seconds, making event creation a breeze.', 'the-events-calendar' ); ?></p>
			</div>
		</div>
		<?php if( ! class_exists( 'Tribe__Events__Pro__Main' ) ): ?>
			<div class="tec-update-page-upgrade-notice tec-update-page-box">
				<img src="<?php echo esc_url( tribe_resource_url( 'lightning.svg', false, 'src/resources/images/icons/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Event series graphic', 'the-events-calendar' ); ?>" />
				<p><?php printf( wp_kses( __( 'Upgrade to <a href="%s">Events Calendar Pro</a> to unlock these features.', 'the-events-calendar' ), [ 'a' => [ 'href' => [] ] ] ), esc_url( 'https://theeventscalendar.com/products/wordpress-events-calendar/' ) ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<div class="tec-update-page-resources">
		<div class="tec-update-page-grid">
			<div class="tec-update-page-grid__item tec-update-page-box ">
				<h3>
					<img class="tec-inline-icon" src="<?php echo esc_url( tribe_resource_url( 'bulb.svg', false, 'src/resources/images/icons/', $tec ) ); ?>" alt="<?php esc_attr_e( 'Light bulb icon', 'the-events-calendar' ); ?>" />
					<?php esc_html_e( 'Legacy Views', 'the-events-calendar' ); ?>
				</h3>
				<p><?php esc_html_e( 'We have fully removed the legacy views from The Events Calendar 6.0. Your views have been automatically updated.', 'the-events-calendar' ); ?></p>
				<a href="<?php echo esc_url( tribe_get_events_link() ); ?>"><?php esc_html_e( 'View your calendar', 'the-events-calendar' ); ?></a>
				<a href="https://theeventscalendar.com/knowledgebase/k/v1-deprecation-faqs/"><?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?></a>
			</div>
			<div class="tec-update-page-grid__item tec-update-page-box ">
				<h3><?php esc_html_e( 'Resources', 'the-events-calendar' ); ?></h3>
				<ul>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/using-wordpress-export-tools-to-migrate-events-content/"><?php esc_html_e( 'Migrating Your Site', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/event-series/"><?php esc_html_e( 'Event Series', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/recurring-events-event-series-in-events-calendar-pro/"><?php esc_html_e( 'Creating Recurring Events', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/duplicate-events/"><?php esc_html_e( 'Duplicating Events', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/recurring-events-and-tickets/"><?php esc_html_e( 'Recurring Events & Tickets', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/knowledgebase/k/community-events-with-series/"><?php esc_html_e( 'Community Events & Series', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/category/release-notes/"><?php esc_html_e( 'Release Notes', 'the-events-calendar' ); ?></a></li>
					<li><a href="https://theeventscalendar.com/support/"><?php esc_html_e( 'Get Support', 'the-events-calendar' ); ?></a></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="tec-update-page-callout tec-update-page-box">
		<p><?php echo esc_html__( 'We won\'t post this page again once you skip to The Events Calendar Settings.', 'the-events-calendar' ); ?></p>
		<a href="<?php echo esc_url( $settings_url ) ?>" class="tribe-events-admin-title__link"><?php esc_html_e( 'Take me to Settings ›', 'the-events-calendar' ); ?></a>
	</div>

</div>