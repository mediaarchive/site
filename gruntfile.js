module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            site: {
                options: {
                    cleancss: true
                },
                files: {
                    "public/styles/site/css/style.min.css": "public/styles/site/less/style.less"
                }
            },
        },
        watch: {
            site_less: {
                files: [
                    'public/styles/site/less/**'
                ],
                tasks: ['less:site'],
                options:{
                    livereload:true
                }
            },
        },
    });
    
    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);
    
    grunt.registerTask('default', ['less']);
}