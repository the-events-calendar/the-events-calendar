<?php
$tec_path = Tribe__Events__Main::instance()->plugin_path . __DIR__ . '/troubleshooting/';
	// admin notice
	include_once $tec_path . 'notice.php';
	// intro
	include_once $tec_path . 'introduction.php';
	// detected issues
	include_once $tec_path . 'detected-issues.php';
	// first steps
	include_once $tec_path . 'first-steps.php';
	// common issues
	include_once $tec_path . 'common-issues.php';
	// system information
	include_once $tec_path . 'system-information.php';
	// recent template changes
	include_once $tec_path . 'recent-template-changes.php';
	// recent logs
	include_once $tec_path . 'event-log.php';
	// ea status
	include_once $tec_path . 'ea-status.php';
	// support cta
	include_once $tec_path . 'support-cta.php';
	// footer
	include_once $tec_path . 'footer-logo.php';
?>

<?php // this is inline jQuery / javascript for extra simplicity */ ?>
<script>
	if (
		jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-title' )
			.hasClass( 'active' )
	) {
		jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-card-title.active' )
			.closest( '.tribe-events-admin__issues-found-card' )
			.find( '.tribe-events-admin__issues-found-description' )
			.show();
	}
	jQuery( '.tribe-events-admin__issues-found-card .tribe-events-admin__issues-found-card-title' )
		.on( 'click', function () {
			var $this = jQuery( this );

			if ( jQuery( this ).hasClass( 'active' ) ) {
				$this
					.removeClass( 'active' )
					.closest( '.tribe-events-admin__issues-found-card' )
					.find( '.tribe-events-admin__issues-found-card-description' )
					.slideUp( 200 );
			} else {
				$this
					.addClass( 'active' )
					.closest( '.tribe-events-admin__issues-found-card' )
					.find( '.tribe-events-admin__issues-found-card-description' )
					.slideDown( 200 );
			}
		} );
</script>
