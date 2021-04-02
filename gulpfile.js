const gulp = require("gulp");
const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");

gulp.task("default", function () {
  return gulp.src("src/style.css")
    .pipe(postcss([
      autoprefixer({
        // ☆IEは11以上、Androidは4.4以上
        // その他は最新2バージョンで必要なベンダープレフィックスを付与する設定
        browsers: ["last 2 versions", "ie >= 11", "Android >= 4"],
        cascade: false
      })
    ]))
    .pipe(gulp.dest("css"));
});
