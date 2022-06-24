<?php
namespace Tribe\Events\Admin\Notice;

use TEC\Events\Custom_Tables\V1\Migration\State;
use Tribe__Admin__Notices;
use Tribe__Template;

use function tribe;
use function tribe_asset;

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
	 * Stores the instance of the notice template.
	 *
	 * @since 4.14.17
	 *
	 * @var Tribe__Template
	 */
	protected $template;

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

		$this->add_block_editor_notice();
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

		if ( ! is_admin() ) {
			return false;
		}

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
		$template = $this->get_template();
		return $template->template( 'notices/update-6-0-0', $this->get_template_data(), false );
	}

	/**
	 * HTML for the notice for sites using UTC Timezones.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	private function get_template_data() {
		$data = [
			'title' 		=>	$this->update_title,
			'description' 	=> 	$this->update_description,
			'upgrade_link' 	=> 	$this->upgrade_tab_link,
			'learn_link' 	=> 	$this->learn_more_link
		];

		return $data;
	}

	/**
	 * Get template object.
	 *
	 * @since TBD
	 *
	 * @return \Tribe__Template
	 */
	public function get_template() {
		if ( empty( self::$template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/admin-views' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Add the JS for the admin notice.
	 * 
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_block_editor_notice() {
		tribe_asset(
			tribe( 'tec.main' ),
			'the-events-calendar-update-6-0-0-notice',
			'tec-update-6.0.0-notice.js',
			[],
			'enqueue_block_editor_assets',
			[
				'in_footer'    => true,
				'localize'     => (object) [ 
					'data' => $this->get_template_data(),
					'name' => 'data'
				],
				'conditionals' => [ $this, 'should_display' ],
			]
		);
	}	
}
