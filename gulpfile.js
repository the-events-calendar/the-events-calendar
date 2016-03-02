'use strict';

var gulp   = require( 'gulp' ),
  uglify   = require( 'gulp-uglify' ),
  rename   = require( 'gulp-rename' ),
  cssnano  = require( 'gulp-cssnano' ),
  git      = require( 'gulp-git' ),
  argv     = require( 'yargs' ).argv,
  notify   = require( 'gulp-error-notifier' ).notify,
	zip      = require( 'gulp-vinyl-zip' ).zip,
  fs       = require( 'fs' );

var compress = function() {
  gulp.src( [
    'src/resources/js/*.js',
    '!src/resources/js/*.min.js'
  ] )
    .pipe( uglify() )
    .pipe(
      rename( {
        extname: '.min.js'
      } )
    )
    .pipe( gulp.dest( 'src/resources/js' ) );

  gulp.src( [
    'src/resources/css/*.css',
    '!src/resources/css/*.min.css',
  ] )
    .pipe( cssnano() )
    .pipe(
      rename( {
        extname: '.min.css'
      } )
    )
    .pipe( gulp.dest( 'src/resources/css' ) );
};

gulp.task( 'default', function() {
  compress();
} );

gulp.task( 'package', function() {
  var json = JSON.parse( fs.readFileSync( './package.json' ) ),
    branch,
    returnbranch;

  console.log( json );

  if ( 'undefined' === typeof argv.branch ) {
    notify( new Error( 'ERROR: When packaging, you must provide a branch via --branch' ) );
    return;
  } else {
    branch = argv.branch;
  }

  if ( 'undefined' === typeof argv.returnbranch ) {
    returnbranch = branch;
  } else {
    returnbranch = argv.returnbranch;
  }

  git.checkout( branch, function( error ) {
    if ( error ) {
      notify( new Error( error ) );
      return;
    }

    git.pull( 'origin', branch, {}, function( error ) {
      if ( error ) {
        notify( new Error( error ) );
        return;
      }

      git.updateSubmodule( { args: '--init --recursive' } );
      compress();
      gulp.src( [
        'the-events-calendar.php',
        'src/**/*',
        'common/tribe-autoload.php',
        'common/tribe-common.php',
        'common/readme.txt',
        'common/src/**/*',
        'common/lang/**/*',
        'common/vendor/jquery/*.css',
        'common/vendor/jquery/images/*.png',
        'lang/**/*',
        'license.txt',
        'readme.md',
        'tests.md',
        'readme.txt',
        'vendor/bootstrap-datepicker/css/*.css',
        'vendor/bootstrap-datepicker/js/*.js',
        'vendor/chosen/public/*.js',
        'vendor/chosen/public/*.css',
        'vendor/chosen/public/*.png',
        'vendor/jquery/*.css',
        'vendor/jquery/*.js',
        'vendor/jquery/smoothness/*.css',
        'vendor/jquery/smoothness/images/*.png',
        'vendor/select2/LICENSE',
        'vendor/select2/*.css',
        'vendor/select2/*.js',
        'vendor/select2/*.gif',
        'vendor/select2/*.png',
        'vendor/jquery-placeholder/*.js',
        'vendor/jquery-placeholder/LICENSE*',
        'vendor/jquery-resize/*.js',
        'vendor/jquery-resize/LICENSE*',
        'vendor/tickets/event-tickets.php',
        'vendor/tickets/readme.txt',
        'vendor/tickets/src/**/*',
        'vendor/tickets/lang/**/*',
        'vendor/tickets/common/tribe-autoload.php',
        'vendor/tickets/common/tribe-common.php',
        'vendor/tickets/common/readme.txt',
        'vendor/tickets/common/src/**/*',
        'vendor/tickets/common/lang/**/*',
        'vendor/tribe-common-libraries/**/*',
      ], { base: '.' } )
        .pipe( zip( json._zipname + '.' + json.version + '.zip' ) )
        .pipe( gulp.dest( '../' ) );
    } );
  } );
} );
