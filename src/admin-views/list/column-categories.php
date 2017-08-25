<?php
$categories = get_the_term_list( $this->get( 'event' )->ID, Tribe__Events__Main::TAXONOMY, '', ', ', '' );
if ( $categories ) {
	echo strip_tags( $categories );
} else {
	echo '&mdash;';
}
