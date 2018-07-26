var gulp = require('gulp');
var sass = require('gulp-sass');
var plumber = require('gulp-plumber');
var rename = require("gulp-rename");

// SassとCssの保存先を指定
gulp.task('sass', function(){
  gulp.src('./wordpress_sass/*.scss')
    .pipe(plumber())
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(rename("style.css"))
    .pipe(gulp.dest('../../wp-content/themes/sample/'));
});

//自動監視のタスクを作成(sass-watchと名付ける)
gulp.task('sass-watch', ['sass'], function(){
  var watcher = gulp.watch('./wordpress_sass/*.scss', ['sass']);
  watcher.on('change', function(event) {
  });
});

// タスク"task-watch"がgulpと入力しただけでdefaultで実行されるようになる
gulp.task('default', ['sass-watch']);