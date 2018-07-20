const gulp = require('gulp'),
    apidoc = require('gulp-apidoc');

gulp.task('build:doc', function (done) {
    apidoc({
        src: "./app/",
        dest: "doc/",
        includeFilters: [".*\\.php$"],
        debug: true
    }, done);
});

//======================================================================
// main task entries
//======================================================================
gulp.task('default', [
    'build:doc',
], function () {
    console.log("Gulp task done.");
});