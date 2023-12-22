/*
 * Base Gulp File
 * - 01 - Requirements
 * - 02 - Paths
 * - 03 - Styles
 * - 04 - Scripts
 * - 05 - Exports
 */
/*------------------------------------*\
  01 - Requirements
  Although Gulp inherently does not require any other libraries in order to
  work, other NPM libraries will be used to generate sourcemaps, be able to
  automatically view in a browser and to minify distributed files.
\*------------------------------------*/
const { src, dest, parallel, series, watch } = require("gulp");

const autoprefixer = require("gulp-autoprefixer");
const browserSync = require("browser-sync").create();
const concat = require("gulp-concat");
const clean = require("gulp-clean");
const ignore = require("gulp-ignore");
const plumber = require("gulp-plumber");
const sass = require("gulp-sass")(require("sass"));
const sourcemaps = require("gulp-sourcemaps");
const uglify = require("gulp-uglify");
const webpack = require("webpack-stream");
const webpackCompiler = require("webpack");
const webpackConfig = require("./webpack.config");

/*------------------------------------*\
  02 - Paths
  Paths are defined here as to where Gulp should look for files to compile,
  as well as where to put files that have been compiled already.
\*------------------------------------*/
const paths = {
  editor: {
    src: [`styles/**/*.scss`, "!styles/scss/main.scss"],
    dest: ".",
  },
  styles: {
    src: [`styles/**/*.scss`, "!styles/scss/editor-style.scss"],
    dest: ".",
  },
  scripts: {
    src: `styles/**/*.js`,
    dest: `js`,
  },
};

/*------------------------------------*\
  03 -  Styles
  Define both compilation of SASS files during development and also when ready for Production and final
  build / minification.
\*------------------------------------*/
function cleanStyles() {
  // Clean asset
  src("./style.css", {
    read: false,
    allowEmpty: true,
  }).pipe(clean());
}

function styles() {
  cleanStyles();

  // Compile styles
  return src(paths.styles.src)
    .pipe(sourcemaps.init())
    .pipe(sass().on("error", sass.logError))
    .pipe(
      autoprefixer({
        overrideBrowserslist: ["last 4 versions"],
        cascade: false,
      })
    )
    .pipe(concat("style.css"))
    .pipe(sourcemaps.write("."))
    .pipe(dest("."))
    .pipe(browserSync.stream());
}
function stylesBuild() {
  cleanStyles();

  return src(paths.styles.src)
    .pipe(sass().on("error", sass.logError))
    .pipe(
      autoprefixer({
        overrideBrowserslist: ["last 4 versions"],
        cascade: false,
      })
    )
    .pipe(concat("style.css"))
    .pipe(dest("."));
}

/*------------------------------------*\
  03 -  Editor Styles
  Define both compilation of SASS files during development and also when ready for Production and final build / minification.
\*------------------------------------*/
function editor() {
  // Clean asset
  src("./editor-style.css", {
    read: false,
    allowEmpty: true,
  }).pipe(clean());

  // Compile styles
  return src(paths.editor.src)
    .pipe(sass())
    .on("error", sass.logError)
    .pipe(
      autoprefixer({
        overrideBrowserslist: ["last 4 versions"],
        cascade: false,
      })
    )
    .pipe(concat("editor-style.css"))
    .pipe(dest("."))
    .pipe(browserSync.stream());
}

/*------------------------------------*\
  04 - Scripts
  Define both compilation of JavaScript files during development and also when
  ready for Production and final build / minification. Here, Webpack is
  defined and streamed into the Gulp process.
\*------------------------------------*/
function scripts() {
  return src(paths.scripts.src)
    .pipe(plumber())
    .pipe(webpack(webpackConfig), webpackCompiler)
    .pipe(ignore.exclude(["**/*.map"]))
    .pipe(dest(paths.scripts.dest));
}
function scriptsBuild() {
  return src(paths.scripts.src)
    .pipe(plumber())
    .pipe(webpack(webpackConfig), webpackCompiler)
    .pipe(ignore.exclude(["**/*.map"]))
    .pipe(uglify())
    .pipe(dest(paths.scripts.dest));
}
/*------------------------------------*\
  05 - Exports
  Define both the developmental, "Watch" and final production, "Build"
  processes for compiling files. The final production, "Build" process includes
  minified files.
  The BrowserSync Proxy address is determined by creating a custom version of a .env file, from the .env-example file.
  Here you will specify the exact local address of your website.
\*------------------------------------*/
exports.watch = () => {
  console.log("You are currently in development watch mode.");
  browserSync.init({
    proxy: "http://appserver",
    browser: process.env.BS_BROWSER || "google chrome",
    socket: {
      domain: "https://bs.wordpress-starter.lndo.site", // The node proxy domain you defined in .lando.yaml. Must be https?
      port: 80, // NOT the 3000 you might expect.
    },
    open: false,
    logConnections: true,
  });

  watch(paths.editor.src, series(editor));
  watch(paths.styles.src, series(styles));
  watch(paths.scripts.src, series(scripts));
};

exports.build = (done) => {
  console.log("You are building for production.");
  parallel(editor)(done);
  parallel(stylesBuild)(done);
  parallel(scriptsBuild)(done);
};
