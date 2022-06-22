<?php
namespace Tribe\Events\Admin\Notice;

use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Admin__Notices;

/**
 * @internal This class may be removed or changed without notice
 */
class Update {
	/**
	 * Notice Slug on the user options
	 *
	 * @since  TBD
	 * @var string
	 */
	private $learn_more_link = 'https://evnt.is/1b79';
		
	/**
	 * Notice Slug on the user options
	 *
	 * @since  TBD
	 * @var string
	 */
	private $upgrade_tab_link = 'edit.php?post_type=tribe_events&page=tec-events-settings&tab=upgrade';
		
	/**
	 * Notice Slug on the user options
	 *
	 * @since  TBD
	 * @var string
	 */
	private $update_title = 'One more thing left to do...';
		
	/**
	 * Notice Slug on the user options
	 *
	 * @since  TBD
	 * @var string
	 */
	private $update_description = 'To complete this major calendar upgrade, you need to migrate your events to the new data storage system. Once migration finishes, you can take advantage of all the cool new 6.0 features!';
		
	/**
	 * Notice
	 *
	 * @since  TBD
	 * @var obj
	 */
	private $notice;

	/**
	 * Register update notices.
	 *
	 * @since TBD
	 * @since 5.1.5 - add Virtual Events Notice.
	 */
	public function register() {
		$this->notice = tribe_notice(
			'update-6-0',
			[ $this, 'notice' ],
			[
				'dismiss' => 1,
				'type'    => 'info',
				'wrap'    => false,
				'recurring' => true,
				'recurring_interval' => 'P1M'
			],
			[ $this, 'should_display' ]
		);

		add_action( 'admin_enqueue_scripts', [ $this, 'add_block_editor_notice' ] );
	}

	/**
	 * Should the notice be displayed?
	 * 
	 * @since TBD
	 *
	 * @return bool
	 */
	public function should_display() {
		$admin_helpers = tribe( 'admin.helpers' );

		if ( isset( $_GET['update-message-the-events-calendar'] ) ) {
			return false;
		}

		if ( ! $admin_helpers->is_screen() ) {
			return false;
		}

		if ( ! $admin_helpers->is_post_type_screen() ) {
			return false;
		}

		if ( tribe( State::class )->is_migrated() ) {
			return false;
		}

		if ( Tribe__Admin__Notices::instance()->has_user_dismissed( $this->notice->slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function notice() {
		ob_start();
		?>
		<div class="tec-update-notice">
			<h3 class="tec-update-notice__title">
				<?php echo esc_html( $this->update_title ); ?>
			</h3>
			<div class="tec-update-notice__description">
				<?php echo esc_html( $this->update_description  ); ?>
			</div>
			<div class="tec-update-notice__actions">
				<a class="tec-update-notice__button button" href="<?php echo esc_url( get_admin_url( null, $this->upgrade_tab_link ) ); ?>">
					<?php esc_html_e( 'Migrate your site', 'the-events-calendar' ); ?>
				</a>
				<a class="tec-update-notice__link" href="<?php echo esc_url( $this->learn_more_link ); ?>">
					<?php esc_html_e( 'Learn more', 'the-events-calendar' ); ?>
				</a>
			</div>
		</div>
		<?php
		$notice = ob_get_contents();
		ob_end_clean();
		
		return $notice;
	}

	/**
	 * Add the JS for the admin notice.
	 * 
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_block_editor_notice() {
		if ( ! $this->should_display() ) {
			return;
		}

		global $current_screen;
		$current_screen = get_current_screen();

		if ( method_exists( $current_screen, 'is_block_editor') && $current_screen->is_block_editor() ) {
			wp_add_inline_script( 'tribe-events-editor', $this->js_notice() );
		}
	}

	/**
	 * Javascript for creating admin notice in the block editor.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function js_notice() {
		$js = '( function ( wp ) {';
		$js .= 'const tec_update_description = "<b>' . esc_html( $this->update_title ) . '</b><p>' . esc_html( $this->update_description ) . '</p>";';
		$js .= 'const tec_upgrade_tab_link = "' . esc_url( $this->upgrade_tab_link ) . '";';
		$js .= 'const tec_learn_more_link = "' . esc_url( $this->learn_more_link ) . '";';
		$js .=	"wp.data.dispatch( 'core/notices' ).createNotice('info', tec_update_description, { __unstableHTML: true, isDismissible: true, actions: [ { url: tec_upgrade_tab_link, label: 'Start storage migration' }, { url: tec_learn_more_link, label: 'Learn more' } ] } );";
		$js .= '} )( window.wp );';

		return $js;
	}	
}
