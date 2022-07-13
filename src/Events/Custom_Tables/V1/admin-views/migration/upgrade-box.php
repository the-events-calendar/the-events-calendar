<?php
/**
 * The template that will be used to render the Migration box.
 *
 * @since TBD
 *
 * @var string            $phase The current Migration phase the site is in.
 * @var Upgrade_Tab       $tab   The Tab we are hosting this template.
 * @var String_Dictionary $text  Instance of our Dictionary of String.
 */

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;

$is_outdated_pro = class_exists( 'Tribe__Events__Pro__Main' ) && version_compare( Tribe__Events__Pro__Main::VERSION, '6.0.0-beta4-dev', '<=' );
?>

<?php if ( $is_outdated_pro ) : ?>
<div class="tec-ct1-upgrade-outdated-pro">
	<?php esc_html_e( 'You must update your version of Events Calendar Pro for access to calendar upgrades.', 'the-events-calendar' ) ?>
	<p><strong>@TODO Waiting on extra copy from PO and design for this BOX.</strong></p>
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