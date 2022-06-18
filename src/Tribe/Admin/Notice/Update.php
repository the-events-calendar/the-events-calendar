<?php
use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * @internal This class may be removed or changed without notice
 */
class Tribe__Events__Admin__Notice__Update {
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
	private $update_description = 'To complete your update to The Events Calendar 6.0 migrate your events to our new data storage system and start taking advantage of the new features.';

	/**
	 * Register update notices.
	 *
	 * @since TBD
	 * @since 5.1.5 - add Virtual Events Notice.
	 */
	public function hook() {
		$notice = tribe_notice(
			'update-6-0',
			[ $this, 'notice' ],
			[
				'dismiss' => 1,
				'type'    => 'info',
				'wrap'    => false,
				'recurring' => true,
				'recurring_interval' => 'M'
			],
			[ $this, 'should_display' ]
		);

		$should_display_js = ! Tribe__Admin__Notices::instance()->has_user_dismissed( $notice->slug );
		
		if ( $should_display_js ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'add_block_editor_notice' ] );
		}
	}

	/**
	 * Add the JS for the admin notice.
	 * 
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_block_editor_notice() {

		$should_display_js = ! Tribe__Admin__Notices::instance()->has_user_dismissed( $notice->slug );

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
	 * Should the notice be displayed?
	 * 
	 * @since TBD
	 *
	 * @return bool
	 */
	private function should_display() {
		if ( tribe( State::class )->is_migrated() ) {
			return false;
		}

		/** @var Tribe__Admin__Helpers $admin_helpers */
		$admin_helpers = tribe( 'admin.helpers' );
		return ( $admin_helpers->is_screen() || $admin_helpers->is_post_type_screen() );
	}

	/**
	 * Get the URL to the upgrade tab.
	 * 
	 * @since TBD
	 *
	 * @return string
	 */	
	private function get_upgrade_tab_link() {
		return get_admin_url( null, $this->upgrade_tab_link );
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
				<a class="tec-update-notice__button button" href="<?php echo esc_url( $this->get_upgrade_tab_link() ); ?>">Start storage migration</a>
				<a class="tec-update-notice__link" href="<?php echo esc_url( $this->learn_more_link ); ?>">Learn more</a>
			</div>
		</div>
		<?php
		$notice = ob_get_contents();
		ob_end_clean();
		
		return $notice;
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
