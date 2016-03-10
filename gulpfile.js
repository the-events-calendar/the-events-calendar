/**
 * This is a simple gulp file that loads gulp tasks from a tasks directory.
 * Whee.
 */
require( 'require-dir' )( './dev/tasks', { recurse: true } );
