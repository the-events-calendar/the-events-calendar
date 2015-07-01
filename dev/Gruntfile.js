module.exports = function(grunt) {
	'use strict';

	/**
	 *
	 * Function to return object from grunt task options stored as files in the "grunt_options" folder.
	 *
	 */

	function load_config(path) {

		var glob = require('glob'),
			object = {},
			key;

		glob.sync('*', {cwd: path}).forEach(function(option) {
			key = option.replace(/\.js$/,'');
			object[key] = require(path + option);
		});

		return object;
	}

	/**
	 *
	 * Start up config by reading from package.json.
	 *
	 */

	var config = {
		pkg: grunt.file.readJSON('package.json')
	};

	/**
	 *
	 * Extend config with all the task options in /options based on the name, eg:
	 * watch.js => watch{}
	 *
	 */

	grunt.util._.extend(config, load_config('./grunt_options/'));

	/**
	 *
	 *  Apply config to Grunt.
	 *
	 */

	grunt.initConfig(config);

	/**
	 *
	 * Usually you would have to load each task one by one.
	 * The load grunt tasks module installed here will read the dependencies/devDependencies/peerDependencies in your package.json
	 * and load grunt tasks that match the provided patterns, eg "grunt" below.
	 *
	 */

	require('load-grunt-tasks')(grunt);

	if ( grunt.option( 'branch' ) ) {
		grunt.config.data.gitcheckout.dist.options.branch = grunt.option( 'branch' );
	}

	if ( grunt.option( 'returnbranch' ) ) {
		grunt.config.data.gitcheckout.dev.options.branch = grunt.option( 'returnbranch' );
	}

	/**
	 *
	 * Now we need to set grunt base to parent directory since we wrapped up our tools in the dev folder.
	 *
	 */

	grunt.file.setBase('../');

	/**
	 *
	 * Tasks are registered here. Starts with default, which is run by simply running "grunt" in your cli.
	 * All other use grunt + taskname.
	 *
	 */

	grunt.registerTask(
		'default', [
			'clean:resourcecss',
			'jshint',
			'preprocess',
			'uglify',
			'cssmin',
			'clean:resourcescripts'
		]);

	grunt.registerTask(
		'lint', [
			'jshint',
			'csslint'
		]);

	grunt.registerTask(
		'package', [
			'gitcheckout:dist',
			'gitpull:dist',
			'copy:dist',
			'compress:dist',
			'clean:dist',
			'gitcheckout:dev'
		]);

};
