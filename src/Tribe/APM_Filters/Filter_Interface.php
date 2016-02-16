<?php


interface Tribe__Events__Pro__APM_Filters__Filter_Interface {

	public function form_row( $return, $key, $value, $unused_filter );

	public function maybe_set_active( $return, $key, $filter );

	public function parse_query( $wp_query_current, $active );

	public function where( $where, $wp_query );
}