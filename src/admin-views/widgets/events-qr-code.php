<?php
/**
 * Admin View: QR Code Widget Form
 *
 * @since 6.12.0
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

use Tribe\Widget\Widget_Abstract;

/**
 * @var Widget_Abstract $widget_obj The widget object.
 * @var array<string,mixed> $admin_fields The admin fields to render.
 */

$this->template( 'widgets/components/form', [ 'admin_fields' => $admin_fields ] );
