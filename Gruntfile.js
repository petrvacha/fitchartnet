module.exports = function(grunt) {
    grunt.initConfig({
        less: {
            web: {
                options: {
                    yuicompress: true
                },
                files: {
                    './htdocs/css/style.min.css': [
                        './htdocs/css/libs/bootstrap-theme.css',
                        './htdocs/css/libs/bootstrap.css',
                        './htdocs/css/libs/jquery.datetimepicker.css',
                        './htdocs/css/style.css',
                    ]
                }
            },
        },

        uglify: {
            web: {
                src: [
                    './htdocs/js/libs/jquery-2.1.4.js',
                    './htdocs/js/libs/jquery.flot.js',
                    './htdocs/js/libs/jquery.flot.time.js',
                    './htdocs/js/libs/netteForms.js',
                    './htdocs/js/libs/jquery.datetimepicker.js',
                    './htdocs/js/libs/bootstrap.min.js',
                    './htdocs/js/common.js',
                ],
                dest: './htdocs/js/script.min.js'
            }
        },

        watch: {
            web_css:{
                files: ['./htdocs/css/**', '!./htdocs/css/*.min.css'],
                tasks: ['less:web']
            },
            web_js:{
                files: ['./htdocs/js/*', '!./htdocs/js/*.min.js'],
                tasks: ['uglify:web']
            },
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['less', 'uglify']);
    grunt.registerTask('build', ['default']);
};
