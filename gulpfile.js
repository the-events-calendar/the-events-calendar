'use strict';

var gulp   = require( 'gulp' ),
  uglify   = require( 'gulp-uglify' ),
  rename   = require( 'gulp-rename' ),
  cssnano  = require( 'gulp-cssnano' ),
  git      = require( 'gulp-git' ),
  argv     = require( 'yargs' ).argv,
  notify   = require( 'gulp-error-notifier' ).notify,
  zip      = require( 'gulp-vinyl-zip' ).zip,
  fs       = require( 'fs' ),
  request  = require( 'request' ),
  download = require( 'gulp-download-stream' );

gulp.task( 'compress-js', function() {
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
} );

gulp.task( 'compress-css', function() {
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
} );

gulp.task( 'glotpress', function() {
  var json = JSON.parse( fs.readFileSync( './package.json' ) );

  var options = {
    domainPath : json._domainPath,
    url        : json._glotPressUrl,
    slug       : json._glotPressSlug,
    textdomain : json._textDomain,
    file_format: json._glotPressFileFormat,
    formats    : json._glotPressFormats,
    filter     : json._glotPressFilter
  };

  var api_url = options.url + '/api/projects/' + options.slug;

  request( api_url, function(error, response, body) {
    if ( ! error && response.statusCode === 200 ) {
      var data = JSON.parse( body );
      var set, index, format;

      for ( index in data.translation_sets ) {
        set = data.translation_sets[ index ];

        if ( 0 === set.current_count ) {
          continue;
        }

        if ( options.filter.minimum_percentage > parseInt( set.percent_translated ) ) {
          continue;
        }

        console.log( set );
        for ( format in options.formats ) {

          var url = api_url + '/' + set.locale + '/' + set.slug + '/export-translations?format=' + options.formats[ format ];
          var info = {
            domainPath : options.domainPath,
            textdomain : options.textdomain,
            locale     : set.locale,
            wp_locale  : set.wp_locale,
            format     : options.formats[ format ]
          };

          if ( ! info.wp_locale ) {
            info.wp_locale = info.locale;
          }

          var filename = options.file_format.replace( /%(\w*)%/g, function( m, key ) {
            return info.hasOwnProperty( key ) ? info[ key ] : '';
          } );

          download( {
            file: filename,
            url: url
          } )
            .pipe( gulp.dest( 'lang/' ) );
        }
      }
    }
  } );
} );

gulp.task( 'pull', function() {
  var branch,
    returnbranch;

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
    } );
  } );
} );

gulp.task( 'zip', function() {
  var json = JSON.parse( fs.readFileSync( './package.json' ) );
  var zip_include = JSON.parse( fs.readFileSync( './dev/zip.json' ) );

  gulp.src( zip_include, { base: '.' } )
    .pipe( zip( json._zipname + '.' + json.version + '.zip' ) )
    .pipe( gulp.dest( '../' ) );
} );

gulp.task( 'default', [ 'compress-js', 'compress-css' ] );
gulp.task( 'package', [ 'pull', 'default', 'glotpress' ] );
