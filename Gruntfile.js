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
                    ]
                }
            },
        },

        uglify: {
            web: {
                src: [
                    './htdocs/js/main.js',
                ],
                dest: './htdocs/js/main.min.js'
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
