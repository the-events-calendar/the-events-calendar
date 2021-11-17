/**
 * This is a simple gulp file that loads gulp tasks from a tasks directory.
 * Whee.
 */
var gulp = require( 'gulp' );
var pkg = require( './package.json' );

require( '@the-events-calendar/product-taskmaster' )( gulp, pkg );
