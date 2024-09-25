<?php
/**
 * The default sidebar for the settings pages.
 *
 * @since 6.7.0
 */

declare( strict_types=1 );

use TEC\Common\Admin\Entities\Br;
use TEC\Common\Admin\Entities\Heading;
use TEC\Common\Admin\Entities\Image;
use TEC\Common\Admin\Entities\Link;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;
use TEC\Common\Admin\Settings_Section;
use TEC\Common\Admin\Settings_Sidebar;
use Tribe\Utils\Element_Attributes as Attributes;
use Tribe\Utils\Element_Classes as Classes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$break               = new Br();
$external_attributes = new Attributes(
	[
		'target' => '_blank',
		'rel'    => 'noopener',
	]
);

$sidebar = new Settings_Sidebar();
$sidebar->set_header_image(
	new Image(
		tribe_resource_url( 'images/settings_illustration.jpg', false, null, Tribe__Events__Main::instance() ),
		new Attributes(
			[
				'alt'  => '',
				'role' => 'presentation',
			]
		)
	)
);

$sidebar->set_title( new Heading( __( 'Finding and extending your calendar', 'tribe-common' ), 2, new Classes( 'tec-settings-form__sidebar-header' ) ) );
$sidebar->add_section(
	( new Settings_Section() )
		->add_elements(
			[
				( new Paragraph() )->add_child(
					new Plain_Text( __( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales, and more?', 'tribe-common' ) )
				),
				new Link(
					admin_url( 'edit.php?post_type=tribe_events&page=tribe-app-shop' ),
					__( 'Check out the available add-ons.', 'tribe-common' )
				),
			]
		)
);

$sidebar->add_section(
	( new Settings_Section() )
		->set_title( new Heading( __( 'Documentation', 'tribe-common' ), 3 ) )
		->add_elements(
			[
				new Link(
					tribe( 'tec.main' )->settings()->get_url( [ Tribe__Events__Main::instance()->activation_page->welcome_slug => 1 ] ),
					__( 'View Welcome Page', 'tribe-common' )
				),
				$break,
				new Link(
					'https://evnt.is/1bbv',
					__( 'Getting started guide', 'tribe-common' ),
					null,
					$external_attributes
				),
				$break,
				new Link(
					'https://evnt.is/1bbw',
					__( 'Knowledgebase', 'tribe-common' ),
					null,
					$external_attributes
				),
			]
		)
);

$sidebar->add_section(
	( new Settings_Section() )
		->add_elements(
			[
				( new Paragraph() )->add_child(
					new Plain_Text( __( 'Where is my calendar?', 'tribe-common' ) )
				),
				new Link(
					tribe( 'tec.main' )->getLink(),
					__( 'Right here', 'tribe-common' )
				),
			]
		)
);

$sidebar->add_section(
	( new Settings_Section() )
		->add_elements(
			[
				( new Paragraph() )->add_child(
					new Plain_Text( __( 'Having trouble?', 'tribe-common' ) )
				),

				new Link(
					admin_url( 'edit.php?post_type=tribe_events&page=tec-events-help' ),
					__( 'Help', 'tribe-common' )
				),
				$break,
				new Link(
					admin_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ),
					__( 'Troubleshoot', 'tribe-common' )
				),
			]
		)
);

return $sidebar;
