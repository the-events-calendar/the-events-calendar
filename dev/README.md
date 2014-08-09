#  === Dev Setup ===

This project uses Node.js, npm and grunt for task management.
All cli commands found below should be executed at the root of this dev directory, NOT the project root.

##  === Prerequisites ===

If you don't already have Node.js installed, please do so first:

[Download Node.js](http://nodejs.org/download/)

The only requirement for Grunt is Grunts CLI(Command Line Utility).
If you don't already have that installed, install globally, this is not project specific.
In NPM we do so with the -g flag.

	npm install -g grunt-cli

Once you have node and grunt cli ready, please install the task modules for this project.
This is done with the command:

    npm install

If you run into any issues with some of the tasks down the road run npm rebuild and try again.

	npm rebuild

For grunt specific dependencies, such as compass or sass, check the grunt task dependencies area below.


# === Noding ===

npm is the official package manager for Node.js.
npm runs through the command line and manages dependencies for an application/build process.
We use it here to install grunt and its tasks.

Some nice tips on working with npm [here](http://howtonode.org/introduction-to-npm).

##  === The Package JSON ===

npm modules are registered in the dev/package.json as "dependencies" and "devDependancies" in this case.
Outside of that object you will note something like this:

	"name": "tribe-build",
      "version": "1.0.0",
      "repository":"git@github.com:moderntribe/the-events-calendar.git",
      "_bowerpath": "dev/bower_components",
      "_resourcepath": "resources",
      "_componentpath": "dev/dev_components",
      "engines": {
        "node": "0.10.30",
        "npm": "1.4.23"
      },

Of note here are the keys that begin with an underscore. These are variables for our use in the packages, in this case Grunt tasks generally.
You can add more as you need for new directories or other uses.
For example, we can use them in Grunt tasks like so:

	libs: {
    		src: [
    			'<%= pkg._resourcepath %>/debug/ba-debug.js',
    			'<%= pkg._resourcepath %>/jquery-ui/ui/jquery.ui.core.js',
    			'<%= pkg._resourcepath %>/jquery-ui/ui/jquery.ui.effect.js',
    			'<%= pkg._resourcepath %>/jquery.fitvids/jquery.fitvids.js'
    		],
    		dest: '<%= pkg._resourcepath %>/libs.js'
    	},

When installing new packages make sure you add the flag `--save-dev` to add them to the package.json file.

# === Grunting ===

##  === Helpful Grunt Primers ===

* [Grunt basics](http://gruntjs.com/getting-started) Getting started with Grunt, if you have no idea what grunt is.
* [Using the grunt CLI](http://gruntjs.com/using-the-cli) The commands you can use in grunt cli.

##  === Grunt Task Dependencies ===

The installed grunt tasks require these external dependencies. As you add new ones document them here.

none so far


##  === Running Grunt Tasks ===

All current tasks are listed here. As you add a new task, document it here.
Run these tasks in terminal/command prompt IN THE DEV DIRECTORY.

We run tasks by starting with

	grunt

in the cli. Just using `grunt` will run the default task if we don't also specify a particular task by name following the command.

Here we will run the global watch task (to do things like compile css when you change it, compile js when you change it etc),
which will run ALL SUB TASKS IN THE WATCH.JS FILE.

	grunt watch

Now lets run the watch task JUST on the theme css, because i'm only working there today.
(this is not necessary, watch is smart and only compiles what it needs to, this is just an example)

	grunt watch:themecss

So to review, `grunt` runs all, `grunt taskname` runs a task with all of its subtasks, and `grunt taskname:subtaskname` runs the specific subtask.

Refer to the watch task in grunt_options/watch.js to understand how the structure and these commands relate.

##  === Defined Grunt Tasks ===

If you add a new task to this project, document it here!

* `grunt watch:themecss` watches the theme scss folder for changes and uses compass to compile it.

##  === Installed Grunt Task Libraries ===

Installed grunt plugins and their documentation links:

* [grunt-contrib-copy](https://npmjs.org/package/grunt-contrib-copy) Copy files.
* [grunt-contrib-concat](https://npmjs.org/package/grunt-contrib-concat) Concatenate files.
* [grunt-contrib-jshint](https://npmjs.org/package/grunt-contrib-jshint) Validate js files with JSHint.
* [grunt-contrib-watch](https://npmjs.org/package/grunt-contrib-watch) Run predefined tasks whenever watched file patterns are added, changed or deleted.
* [grunt-contrib-clean](https://npmjs.org/package/grunt-contrib-clean) Clean files and folders.
* [grunt-contrib-uglify](https://npmjs.org/package/grunt-contrib-uglify) Minify files with UglifyJS.
* [grunt-newer](https://npmjs.org/package/grunt-newer) Run Grunt tasks with only those source files modified since the last successful run.
* [grunt-contrib-csslint](https://www.npmjs.org/package/grunt-contrib-csslint) Lint CSS files.
* [grunt-contrib-cssmin](https://www.npmjs.org/package/grunt-contrib-cssmin) Minify css.
* [grunt-contrib-compress](https://github.com/gruntjs/grunt-contrib-compress) Package a zip file for release.
* [grunt-preprocess](https://npmjs.org/package/grunt-preprocess) Preprocess HTML, JavaScript etc directives based off environment configuration.

##  === Installing Your Own Grunt Task ===

You'll find them in grunts repolist, npm and on github.
Search here first: [Grunt Plugins Directory](http://gruntjs.com/plugins)

Don't follow all of a tasks instructions for install. :P We've changed things a bit, but dont't worry, these changes actually make it easier for you.

Run the install as they define, but don't add the load task line to the gruntfile, our system autoloads. Eg a line like this:

	grunt.loadNpmTasks('adapt-grunt-tracking-ids');

Is not needed here.

To define your tasks, don't add them to the gruntfile.js. Instead, create a new js file in the dev/grunt_options folder with a filename of the task.
In the example task above, its task name is `adapt_insert_tracking_ids` so we make a file called "adapt_insert_tracking_ids.js".
Inside that file we wrap our task in this:

	module.exports = {

		// tasks here

	};

Check the existing files for reference. Also, please note the existing comments in each task file and emulate.

Your last step is to register your task, if you need to.
Your task can be called without registering with `grunt taskname` but you can also add it to a build flow, and probably will want to.
This is done at the bottom of the gruntfile.js.
Here is an example of a task that executes a range of subtasks relating to our theme.

	grunt.registerTask(
		'theme', [
			'copy:movelibs',
			'concat:themelibs',
			'concat:themescripts',
			'preprocess:theme',
			'uglify:theme',
			'clean:theme',
			'modernizr:theme',
			'compass:theme'
		]);

We can run that group with `grunt theme`.

