<?php

foreach($data as $event){

	echo $event->post_title;

	echo round( $event->distance, 2 );

}
