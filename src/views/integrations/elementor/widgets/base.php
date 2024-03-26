<?php
/**
 * View: Elementor base widget.
 *
 * You can override this template in your own theme by creating a file at
 * [your-theme]/tribe/events/integrations/elementor/widgets/base.php
 *
 * @since TBD
 *
 * @var Template_Engine $this The template engine.
 */

use TEC\Events_Pro\Integrations\Plugins\Elementor\Widgets\Template_Engine;

$this->template( $this->get_widget()->get_template_file() );
