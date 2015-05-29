/**
 *
 * Module: grunt-contrib-csslint
 * Documentation: https://npmjs.org/package/grunt-contrib-csslint
 * Example:
 *

    strict: {
	    options: {
	      import: 2
	    },
	    src: ['path/to.css']
	},
	 lax: {
		options: {
		import: false
		},
		src: ['path/to.css']
	}

 *
 */

module.exports = {

	resourcecss: {

		options: {
			import: false,
			force:true
		},
		src: ['<%= pkg._resourcepath %>/css/*.css', '<%= pkg._resourcepath %>/css/!*.min.css']
	}

};
