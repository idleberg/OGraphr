 /*
  * OGraphr-devkit
  * https://github.com/idleberg/OGraphr
  *
  * Copyright (c) 2014 Jan T. Sott
  * Licensed under the MIT license.
  */

var meta = require('./package.json');

var del     = require('del');
var cache   = require('gulp-cached');
var concat  = require('gulp-concat');
var csslint = require('gulp-csslint');
var cssmin  = require('gulp-css');
var gulp    = require('gulp');
var jshint  = require('gulp-jshint');
// var phplint = require('phplint').lint;
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
gulp.task(    'lint', ['csslint', 'jshint']);
gulp.task(    'make', ['cssmin', 'uglify']);
// gulp.task(     'php', ['phplint']);
gulp.task(  'travis', ['csslint', 'jshint']);

/*
 * Sub-tasks
 */

// PHP Code
// gulp.task('phplint', function(cb) {
//   phplint([
//       'admin.php',
//       'config.php',
//       'index.php'
//     ], {limit: 10}, function (err, stdout, stderr) {
//     if (err) {
//       cb(err);
//       process.exit(1);
//     }
//     cb();
//   });
// });

// Custom CSS
gulp.task('cssmin', ['cssclean'], function() {
  gulp.src([
      'bower_components/jqplot-bower/dist/jquery.jqplot.min.css',
      'src/style.css'
    ])
    .pipe(concat('styles.min.css'))
    .pipe(cssmin())
    .pipe(gulp.dest('./assets/'))
});

gulp.task('csslint', function() {
  return gulp.src([
      './src/style.css'
    ])
    .pipe(cache('linting_css'))
    .pipe(csslint({
      'overqualified-elements': false
    }))
    .pipe(csslint.reporter())
});

// Custom Javascript
gulp.task('uglify', ['jsclean'], function() {
  gulp.src([
      'bower_components/jqplot-bower/dist/jquery.jqplot.min.js',
      'bower_components/jqplot-bower/dist/plugins/jqplot.dateAxisRenderer.min.js',
      'bower_components/jqplot-bower/dist/plugins/jqplot.highlighter.min.js',
      'src/scripts.js'
    ])
    .pipe(concat('scripts.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('./assets/'))
});

gulp.task('jshint', function() {
  return gulp.src([
      './src/scripts.js'
    ])
    .pipe(cache('linting_js'))
    .pipe(jshint())
    .pipe(jshint.reporter())
});

// Cleaning tasks
gulp.task('cssclean', function () {
    del([
      './assets/*.css'
    ])
});

gulp.task('jsclean', function () {
    del([
      './assets/*.js'
    ])
});

// Watch task
gulp.task('watch', function () {
   gulp.watch([
            './admin.php',
            './config.php',
            './index.php',
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
  console.log('         lint - run tasks to lint all CSS, JavaScript and PHP files')
  console.log('         make - minify all CSS and JavaScript files')
  console.log('\nFor further details visit the GitHub repository:')
  console.log(meta.homepage)

} )