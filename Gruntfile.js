module.exports = function(grunt) {

   // Project configuration.
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),
      sass: {
         dist: {
            files: {
               'assets/styles/main.css': 'assets/styles/main.scss'
            }
         }
      },
      concat: {
         h5bpStyles: {
            src: 'assets/styles/h5bp/!(h5bp-built).css',
            dest: 'assets/styles/h5bp/h5bp-built.css'
         },
         styles: {
            src: 'assets/styles/!(klein-pages-built).css',
            dest: 'assets/styles/klein-pages-built.css'
         },
         scripts: {
            src: 'assets/scripts/!(klein-pages-built).js',
            dest: 'assets/scripts/klein-pages-built.js'
         }
      },
      uglify: {
         options: {
            banner: '/** <%= pkg.name %> - built <%= grunt.template.today("yyyy-mm-dd HH:MM") %> **/\n'
         },
         build: {
            src: 'assets/scripts/klein-pages-built.js',
            dest: 'assets/scripts/klein-pages-built.min.js'
         }
      },
      watch: {
         sass: {
            files: 'assets/styles/**/*.scss',
            tasks: ['sass']
         },
         scripts: {
            files: [
               'assets/styles/h5bp/!(h5bp-built).css',
               'assets/styles/!(klein-pages-built).css',
               'assets/scripts/!(klein-pages-built).js',
            ],
            tasks: ['concat']
         },
         jekyllSiteFiles: {
            files: [
               '*.html',
               '*.md',
               '_layouts/**',
               '_posts/**',
               '_includes/**',
               'assets/**/(h5bp-built).*',
               'assets/**/(klein-pages-built).*',
               'assets/styles/main.css'
            ],
            tasks: 'shell:jekyll'
         }
      },
      shell: {
         jekyll: {
            command: 'jekyll',
            stdout: true
         }
      }
   });

   // Load our grunt tasks
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-concat');
   grunt.loadNpmTasks('grunt-contrib-uglify');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-shell');

   // Default task(s).
   grunt.registerTask('default', ['sass', 'concat']);
   grunt.registerTask('dist', ['sass', 'concat', 'uglify', 'shell']);

};
