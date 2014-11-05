 /*
  * OGraphr-devkit
  * https://github.com/idleberg/OGraphr
  *
  * Copyright (c) 2014 Jan T. Sott
  * Licensed under the MIT license.
  */

var meta = require('./package.json');

var del     = require('del');
var concat  = require('gulp-concat');
var csslint = require('gulp-csslint');
var cssmin  = require('gulp-css');
var gulp    = require('gulp');
var jshint  = require('gulp-jshint');
var phplint = require('phplint');
var uglify  = require('gulp-uglify');
var util    = require('gulp-util');
var watch   = require('gulp-watch');

/*
 * Task combos
 */
gulp.task(   'clean', ['cssclean', 'jsclean']);
gulp.task(     'css', ['csslint', 'cssmin']);
gulp.task( 'default', ['make']);
gulp.task(      'js', ['jshint', 'uglify']);
gulp.task(    'lint', ['csslint', 'jshint', 'phplint']);
gulp.task(    'make', ['cssmin', 'jqplot', 'uglify']);
gulp.task(     'php', ['phplint']);
gulp.task(  'travis', ['csslint', 'jshint']);

/*
 * Sub-tasks
 */

// PHP Code
gulp.task('phplint', function () {
  return phplint([
        './meta-ographr_admin.php',
        './meta-ographr_index.php'
    ]);
});

// Custom CSS
gulp.task('cssmin', ['cssclean'], function() {
  gulp.src([
      './src/style.css'
    ])
    .pipe(concat('./style.min.css'))
    .pipe(cssmin())
    .pipe(gulp.dest('./app/'))
});

gulp.task('csslint', function() {
  gulp.src([
      './src/style.css'
    ])
    .pipe(csslint())
    .pipe(csslint.reporter())
});

// Custom Javascript
gulp.task('uglify', ['jsclean'], function() {
  gulp.src([
      './src/scripts.js'
    ])
    .pipe(uglify())
    .pipe(concat('./scripts.min.js'))
    .pipe(gulp.dest('./app/'))
});

gulp.task('jshint', function() {
  gulp.src([
      './src/scripts.js'
    ])
    .pipe(jshint())
    .pipe(jshint.reporter())
});

// jqplot (http://www.jqplot.com/)
gulp.task('jqplot', function() {

  gulp.src([
      './bower_components/jqplot-bower/dist/jquery.jqplot.min.css',
      './bower_components/jqplot-bower/dist/jquery.jqplot.min.js',
      './bower_components/jqplot-bower/dist/plugins/jqplot.dateAxisRenderer.min.js',
      './bower_components/jqplot-bower/dist/plugins/jqplot.highlighter.min.js'
    ])
    .pipe(gulp.dest('./app/'));
});

// Cleaning tasks
gulp.task('cssclean', function () {
    del([
      './app/*.css'
    ])
});

gulp.task('jsclean', function () {
    del([
      './app/*.js'
    ])
});

// Watch task
gulp.task('watch', function () {
   gulp.watch([
            './meta-ographr_admin.php',
            './meta-ographr_index.php',
            './src/scripts.js',
            './src/style.css'
         ],
         ['lint'])
});


// Help
gulp.task('help', function() {

  console.log('\n' + meta.name + ' v' + meta.version)
  console.log('======================\n')
  console.log('Available tasks:')
  console.log('         help - this dialog')
  console.log('        clean - delete CSS and JS files')
  console.log('         make - concatenate and minify CSS and JS')
  console.log('         lint - run tasks to lint all CSS, JavaScript and PHP files')
  console.log('         make - minify all CSS and JavaScript files')
  console.log('\nFor further details visit the GitHub repository:')
  console.log(meta.homepage)

} )