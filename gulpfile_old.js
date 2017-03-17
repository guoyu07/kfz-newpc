var gulp = require('gulp'),
        os = require('os'),
        fs = require("fs"),
        concat = require('gulp-concat'),
        cssmin = require('gulp-cssmin'),
        uglify = require('gulp-uglify'),
        md5 = require('gulp-md5-plus'),
        //gutil = require('gulp-util'),
        //fileinclude = require('gulp-file-include'),
        //clean = require('gulp-clean'),
        //base64 = require('gulp-css-base64'),
        //imagemin = require('gulp-imagemin'),
        spritesmith = require('gulp.spritesmith');

//合并图片（图标）
gulp.task('sprite', function (done) {
    //var timestamp = +new Date();
    gulp.src('src/sprites/*')
            .pipe(spritesmith({
                imgName: 'img/icon.png',
                cssName: 'css/icon.css'
            })).pipe(gulp.dest('build/'))
            .on('end', done);
});

//合并首页css
gulp.task('build:css:index', ['sprite'], function (done) {
    gulp.src([
        'src/css/base.css',
        'build/css/icon.css',
        'src/widgets/nav/top/*.css',
        'src/widgets/header/main/*.css',
        'src/widgets/category/main/*.css',
        'src/css/index.css'])
            .pipe(concat('index.min.css'))
            .pipe(cssmin())
            .pipe(gulp.dest('build/css/'))
            .on('end', done);
});

//合并lib中的js
gulp.task('build:lib', function (done) {
    gulp.src([
        'src/libs/jQuery.js'])
            .pipe(concat('lib.js'))
            .pipe(gulp.dest('build/libs/'))
            .on('end', done);
});

//合并首页js
gulp.task('build:js:index', ['build:lib'], function (done) {
    gulp.src([
        'build/libs/lib.js',
        'src/widgets/nav/top/*.js',
        'src/widgets/header/main/*.js',
        'src/widgets/category/main/*.js',
        'src/widgets/banner/main/*.js',
        'src/js/index.js'])
            .pipe(concat('index.min.js'))
            .pipe(uglify())
            .pipe(gulp.dest('build/js/'))
            .on('end', done);
});

//将图片拷贝到目标目录
gulp.task('copy:img', function (done) {
    gulp.src(['build/img/*', 'src/img/*']).pipe(gulp.dest('webroot/img/')).on('end', done);
});

//处理php的app模板
gulp.task('copy:views', function (done) {
    gulp.src(['src/views/*/*.phtml'])
            .pipe(gulp.dest('application/views'))
            .on('end', done);
});

//处理php的widgets模板
gulp.task('copy:phtml', function (done) {
    gulp.src(['src/widgets/*/*/*.phtml'])
            .pipe(gulp.dest('application/views/_widgets_/'))
            .on('end', done);
});

//复制widgets
gulp.task('copy:widgets', function (done) {
    gulp.src(['src/widgets/*/*/*.html', 'src/widgets/*/*/*.css', 'src/widgets/*/*/img/*', 'src/widgets/*/*/*.js'])
            .pipe(gulp.dest('webroot/widgets/'))
            .on('end', done);
});

//将js加上6位md5,并修改html中的引用路径
gulp.task('md5:js', ['build:js:index', 'copy:views'], function (done) {
    gulp.src('build/js/*.min.js')
            //.pipe(md5(6, 'app/*/*.phtml'))
            .pipe(gulp.dest('webroot/js/'))
            .on('end', done);
});

//将css加上6位md5，并修改html中的引用路径
gulp.task('md5:css', ['build:css:index', 'copy:views'], function (done) {
    gulp.src('build/css/*.min.css')
            //.pipe(md5(6, 'app/*/*.phtml'))
            .pipe(gulp.dest('webroot/css/'))
            .on('end', done);
});

gulp.task('copy', ['copy:views', 'copy:widgets', 'copy:img', 'copy:phtml']);

gulp.task('md5', ['md5:js', 'md5:css']);

gulp.task('watch', function (done) {
    gulp.watch('src/**/*', ['copy', 'md5'])
            .on('end', done);
});

//发布
gulp.task('default', ['md5', 'copy']);

//开发
gulp.task('dev', ['md5', 'copy', 'watch']);
