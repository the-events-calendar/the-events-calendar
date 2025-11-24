<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since 6.0.0
 *
 * @var string            $phase The current Migration phase the site is in.
 * @var Upgrade_Tab       $tab   The Tab we are hosting this template.
 * @var String_Dictionary $text  Instance of our Dictionary of String.
 */

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;

$is_outdated_pro = class_exists( 'Tribe__Events__Pro__Main' ) && version_compare( Tribe__Events__Pro__Main::VERSION, '6.0.0-dev', '<=' );
?>

<?php if ( $is_outdated_pro ) : ?>
<div class="tec-ct1-upgrade-outdated-pro tec-ct1-upgrade">
	<div class="tec-ct1-upgrade__row">
		<div class="content-container">
			<h3><?php esc_html_e( 'Upgrade is not Available', 'the-events-calendar' ) ?></h3>
			<p><?php esc_html_e( 'You must update your version of Events Calendar Pro for access to calendar upgrades.', 'the-events-calendar' ) ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ); ?>"><?php esc_html_e( 'Plugins Page', 'the-events-calendar' ); ?></a> |
				<a href="https://evnt.is/ver-compat" target="_blank" rel="nofollow noopener noreferrer"><?php esc_html_e( 'Version Compatibility', 'the-events-calendar' ); ?></a> |
				<a href="https://evnt.is/v6-upgrade-req" target="_blank" rel="nofollow noopener noreferrer"><?php esc_html_e( 'Upgrade FAQ', 'the-events-calendar' ); ?></a>
			</p>
		</div>
	</div>
</div>
<?php else : ?>
<div id="tec-ct1-upgrade-box">
	<div id="tec-ct1-upgrade-dynamic" class="tec-ct1-upgrade">
		<div class="tec-ct1-upgrade__row">
			<div class="content-container">
				<p><?php echo esc_html( $text->get( 'loading-message' ) ); ?></p>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
