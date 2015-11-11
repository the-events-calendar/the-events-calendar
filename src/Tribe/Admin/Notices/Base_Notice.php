<?php


class Tribe__Events__Main__Admin__Notices__Base_Notice implements Tribe__Events__Main__Admin__Notices__Notice_Interface {

	public function render( $message, $class = 'updated' ) {
		printf( '<div class="%s"><p>%s</p></div>', $class, $message );
	}
}