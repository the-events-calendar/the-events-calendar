<?php


class Tribe__Events__Admin__Bar__Default_Configurator implements Tribe__Events__Admin__Bar__Configurator_Interface {

	/**
	 * Configures an admin bar object adding menus, groups and nodes to it.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 *
	 * @return array An array of menus to add to the admin bar.
	 */
	public function configure( WP_Admin_Bar $wp_admin_bar ) {
		$main = Tribe__Events__Main::instance();

		$wp_admin_bar->add_menu( array(
			'id'    => 'tribe-events',
			'title' => '<span class="ab-icon dashicons-before dashicons-calendar"></span>' . sprintf( __( '%s', 'the-events-calendar' ), $main->plural_event_label ),
			'href'  => $main->getLink( 'home' ),
		) );

		$wp_admin_bar->add_group( array(
			'id'     => 'tribe-events-group',
			'parent' => 'tribe-events',
		) );

		$wp_admin_bar->add_group( array(
			'id'     => 'tribe-events-add-ons-group',
			'parent' => 'tribe-events',
		) );

		$wp_admin_bar->add_group( array(
			'id'     => 'tribe-events-settings-group',
			'parent' => 'tribe-events',
		) );
		if ( current_user_can( 'edit_tribe_events' ) ) {
			$wp_admin_bar->add_group( array(
				'id'     => 'tribe-events-import-group',
				'parent' => 'tribe-events-add-ons-group',
			) );
		}

		$wp_admin_bar->add_menu( array(
			'id'     => 'tribe-events-view-calendar',
			'title'  => esc_html__( 'View Calendar', 'the-events-calendar' ),
			'href'   => $main->getLink( 'home' ),
			'parent' => 'tribe-events-group',
		) );

		if ( current_user_can( 'edit_tribe_events' ) ) {
			$wp_admin_bar->add_menu( array(
				'id'     => 'tribe-events-add-event',
				'title'  => sprintf( esc_html__( 'Add %s', 'the-events-calendar' ), $main->singular_event_label ),
				'href'   => trailingslashit( get_admin_url() ) . 'post-new.php?post_type=' . Tribe__Events__Main::POSTTYPE,
				'parent' => 'tribe-events-group',
			) );
		}

		if ( current_user_can( 'edit_tribe_events' ) ) {
			$wp_admin_bar->add_menu( array(
				'id'     => 'tribe-events-edit-events',
				'title'  => sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $main->plural_event_label ),
				'href'   => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . Tribe__Events__Main::POSTTYPE,
				'parent' => 'tribe-events-group',
			) );
		}

		if ( current_user_can( 'publish_tribe_events' ) ) {
			$import_node = $wp_admin_bar->get_node( 'tribe-events-import' );
			if ( ! is_object( $import_node ) ) {
				$wp_admin_bar->add_menu( array(
					'id'     => 'tribe-events-import',
					'title'  => esc_html__( 'Import', 'the-events-calendar' ),
					'parent' => 'tribe-events-import-group',
				) );
			}
			$wp_admin_bar->add_menu( array(
				'id'     => 'tribe-csv-import',
				'title'  => esc_html__( 'CSV', 'the-events-calendar' ),
				'href'   => esc_url( add_query_arg( array(
					'post_type' => Tribe__Events__Main::POSTTYPE,
					'page'      => 'events-importer',
					'tab'       => 'csv-importer',
				), admin_url( 'edit.php' ) ) ),
				'parent' => 'tribe-events-import',
			) );
		}

		if ( current_user_can( 'manage_options' ) ) {

			$hide_all_settings = Tribe__Settings_Manager::get_network_option( 'allSettingsTabsHidden', '0' );
			if ( $hide_all_settings == '0' ) {
				$wp_admin_bar->add_menu( array(
					'id'     => 'tribe-events-settings',
					'title'  => esc_html__( 'Settings', 'the-events-calendar' ),
					'href'   => Tribe__Settings::instance()->get_url(),
					'parent' => 'tribe-events-settings-group',
				) );
			}

			// Only show help link if it's not blocked in network admin.
			$hidden_settings_tabs = Tribe__Settings_Manager::get_network_option( 'hideSettingsTabs', array() );
			if ( ! in_array( 'help', $hidden_settings_tabs ) ) {
				$wp_admin_bar->add_menu( array(
					'id'     => 'tribe-events-help',
					'title'  => esc_html__( 'Help', 'the-events-calendar' ),
					'href'   => Tribe__Settings::instance()->get_url( array( 'tab' => 'help' ) ),
					'parent' => 'tribe-events-settings-group',
				) );
			}
		}
	}
}