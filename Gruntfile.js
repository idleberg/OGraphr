 /*
 * OGraphr-lint
 * https://github.com/idleberg/OGraphr
 *
 * Copyright (c) 2014 Jan T. Sott
 * Licensed under the MIT license.
 */
 
 module.exports = function(grunt) {

    var phpFiles = [
        './meta-ographr_admin.php',
    	'./meta-ographr_index.php'
    ];

 	grunt.initConfig({

 		// default tasks
        phplint: {
            files: phpFiles,
        },

		// watch task
        watch: {
		    php: {
		        files: phpFiles,
		        tasks: ['phplint']
		    },
		}
 	});

 	grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks("grunt-phplint");
 	grunt.registerTask('default', ['phplint']);

    // task shortcuts
 	grunt.registerTask('php', 'phplint');
 };