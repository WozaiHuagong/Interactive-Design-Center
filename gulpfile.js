'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;
var babel = require('gulp-babel');
var browserify = require('gulp-browserify');

gulp.task('sass', function(){
    return gulp.src('./sass/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./css/'))
    .pipe(reload({stream: true}));
}) 

gulp.task('sass:watch', function(){
    gulp.watch('./sass/*.scss', ['sass']);
})

gulp.task('scripts', function() {
    return gulp.src('src/app.js')
        .pipe(browserify({
            insertGlobals : false
        }))
        // .pipe(babel({
        //     presets: ['es2015']
        // }))
        .pipe(gulp.dest('./js/'))
        .pipe(reload({stream: true}));
});

gulp.task('scripts::watch', function() {
    gulp.watch('./src/app.js', ['scripts']);
})

gulp.task('browser-sync', function() {
    browserSync.init({
        server: {
            baseDir: "./"
        }
    });
});

gulp.task('test', function(){
    gulp.watch('./*').on('change', reload);
    gulp.start('browser-sync');
    gulp.start('sass:watch');
    gulp.start('scripts::watch');
})