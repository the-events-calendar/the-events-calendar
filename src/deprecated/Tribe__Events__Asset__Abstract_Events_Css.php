<?php
_deprecated_file( __FILE__, '4.6.21', 'Deprecated class in favor of using `tribe_asset` registration' );

abstract class Tribe__Events__Asset__Abstract_Events_Css {

	abstract public function handle( array &$stylesheets, $mobile_break );

}