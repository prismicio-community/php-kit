var gulp = require('gulp'),
    gist = require('gulp-gist'),
    deploy = require("gulp-gh-pages");

var pkg = require('./package.json');

gulp.task('deploy:doc', function () {
    gulp.src("./doc/**/*")
        .pipe(deploy());
});

gulp.task('deploy:gist', function () {

    gulp.src("./tests/Prismic/DocTest.php")
        .pipe(gist());
});

gulp.task('dist', ['deploy:doc', 'deploy:gist']);

/**
 * Default task
 */

gulp.task('default', ['deploy:gist']);

