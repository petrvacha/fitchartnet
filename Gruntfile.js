module.exports = function(grunt) {
    grunt.initConfig({
        less: {
            web: {
                options: {
                    yuicompress: true
                },
                files: {
                    './htdocs/css/style.min.css': [
                        './htdocs/css/style.css',
                        './htdocs/css/jquery.datetimepicker.css'
                    ]
                }
            },
        },

        uglify: {
            web: {
                src: [
                    './htdocs/js/jquery-2.1.4.js',
                    './htdocs/js/common.js',
                    './htdocs/js/netteForms.js',
                    './htdocs/js/jquery.datetimepicker.js',
                ],
                dest: './htdocs/js/script.min.js'
            }
        },

        watch: {
            web_css:{
                files: ['./htdocs/css/**', '!./css/*.min.css'],
                tasks: ['less:web']
            },
            web_js:{
                files: ['./htdocs/js/*', '!./js/*.min.js'],
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
