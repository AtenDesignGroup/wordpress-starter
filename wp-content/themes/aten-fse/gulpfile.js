/*
 * Base Gulp File
 * - 01 - Requirements
 * - 02 - Paths
 * - 03 - Filename Conversion
 * - 04 - Base Styles
 * - 05 - Base Scripts
 * - 06 - Block Styles
 * - 07 - Block Scripts
 * - 08 - Editor Styles
 * - 09 - Exports
 */
/*------------------------------------*\
  01 - Requirements
  Although Gulp inherently does not require any other libraries in order to
  work, other NPM libraries will be used to generate sourcemaps, be able to
  automatically view in a browser and to minify distributed files.
\*------------------------------------*/
const { src, dest, lastRun, parallel, series, watch } = require('gulp');

const browserSync = require('browser-sync').create();
const concat = require('gulp-concat');
const clean = require('gulp-clean');
const dependents = require('gulp-dependents');
const ignore = require('gulp-ignore');
const named = require('vinyl-named');
const path = require('path');
const postcss = require('gulp-postcss');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');
const webpack = require('webpack-stream');
const webpackCompiler = require('webpack');
const webpackConfig = require('./webpack.config');

/*------------------------------------*\
  02 - Paths
  Paths are defined here as to where Gulp should look for files to compile,
  as well as where to put files that have been compiled already.
\*------------------------------------*/
const paths = {
  base: {
    styles: {
      src: [
        `libraries/main.scss`,
        `libraries/**/*.scss`,
        '!libraries/editor-style.scss',
      ],
      dest: '.',
    },
    scripts: {
      src: `libraries/js/**/*.js`,
      dest: `dist/js`,
    },
  },
  editor: {
    src: [`libraries/**/*.scss`, '!libraries/main.scss'],
    dest: '.',
  },
  blocks: {
    styles: {
      src: 'blocks/*/src/*.scss',
      dest: 'blocks',
    },
    scripts: {
      src: 'blocks/*/src/*.js',
      dest: '.',
    },
  },
};

/*------------------------------------*\
  03 - Filename Conversion
  In order for Webpack to compile multiple entry points, we will need to dynamically update the
  file names of the scripts passed in. Rather than passing an array of entry points (a.k.a. source files)
  to a single webpack instance, we pipe each files to their own instance. This allows for incremental builds
  that only recompile files that have changed. This is a huge performance boost when working with many files.
\*------------------------------------*/

/**
 * Rename a base script file with an absolute source path to a detination path
 * relative to the theme's directory.
 *
 * @param {object} file
 *  Vinyl file object
 * @returns {string}
 *  Relative path to the destination file with the .js extension removed
 */
const renameBaseScripts = (file) => {
  return path.relative(
    paths.base.scripts.src,
    file.path
      .replace('/libraries/js', `/${paths.base.scripts.src}`)
      // Remove the .js extension
      .slice(0, -3)
  );
};

/**
 * Rename a component script file with an absolute source path to base detination path
 * relative to the theme's directory.
 *
 * @param {object} file
 *  Vinyl file object
 * @returns {string}
 *  Relative path to the destination file with the .js extension removed
 */
const renameBlockScripts = (file) => {
  return path.relative(
    process.cwd(),
    file.path
      .replace('/src', '')
      // Remove the .js extension
      .slice(0, -3)
  );
};

/*------------------------------------*\
  04 - Base Styles
  Define both compilation of SASS files during development and also when ready for Production and final
  build / minification.
\*------------------------------------*/
function cleanStyles() {
  // Clean asset
  src('./style.css', {
    read: false,
    allowEmpty: true,
  }).pipe(clean());
}

// Base style watch - This listens for changes in the base styles directory and compiles them.
function baseStylesWatch() {
  cleanStyles();

  // Compile styles
  return src(paths.base.styles.src, {
    sourcemaps: true,
    since: lastRun(baseStylesWatch),
  })
    .pipe(sourcemaps.init())
    .pipe(dependents())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss())
    .pipe(concat('style.css'))
    .pipe(dest(paths.base.styles.dest, { sourcemaps: true }))
    .pipe(browserSync.stream());
}

// Style Build
function baseStylesBuild() {
  cleanStyles();

  return src(paths.base.styles.src)
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss())
    .pipe(concat('style.css'))
    .pipe(dest(paths.base.styles.dest));
}

/*------------------------------------*\
  05 - Base Scripts
  Define both compilation of JavaScript files during development and also when
  ready for Production and final build / minification. Here, Webpack is
  defined and streamed into the Gulp process.

  We first define each task as a function that accepts a callback. This allows us leverage gulp's
  lastRun feature which will only recompile files that have changed since the last time the task was run.
  This greatly increases performance when running gulp watch with many files.
\*------------------------------------*/
function baseScriptsWatch() {
  return src(paths.base.scripts.src, {
    since: lastRun(baseScriptsWatch),
  })
    .pipe(named(renameBaseScripts))
    .pipe(webpack(webpackConfig), webpackCompiler)
    .on('error', function (err) {
      this.emit('end'); // Don't stop the rest of the task
    })
    .pipe(dest(paths.base.scripts.dest));
}
function baseScriptsBuild() {
  return src(paths.base.scripts.src)
    .pipe(named(renameBaseScripts))
    .pipe(webpack(webpackConfig), webpackCompiler)
    .pipe(ignore.exclude(['**/*.map']))
    .pipe(uglify())
    .pipe(dest(paths.base.scripts.dest));
}

/*------------------------------------*\
  06 - Block Styles
  Define both compilation of SASS files during development and also when ready for Production and final
  build / minification.
\*------------------------------------*/

// Block style watch - This listens for changes in the block src directories and compiles them.
function blockStylesWatch() {
  // Compile styles
  return src(paths.blocks.styles.src, {
    sourcemaps: true,
    since: lastRun(blockStylesWatch),
  })
    .pipe(sourcemaps.init())
    .pipe(dependents())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss())
    .pipe(
      rename(function (file) {
        file.dirname = file.dirname.replace('/src', '');
      })
    )
    .pipe(dest(paths.blocks.styles.dest, { sourcemaps: true }))
    .pipe(browserSync.stream());
}

// Style Build
function blockStylesBuild() {
  return src(paths.blocks.styles.src)
    .pipe(named(renameBlockScripts))
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss())
    .pipe(
      rename(function (file) {
        file.dirname = file.dirname.replace('/src', '');
      })
    )
    .pipe(dest(paths.blocks.styles.dest));
}

/*------------------------------------*\
  07 - Block Scripts
  Define both compilation of JavaScript files during development and also when
  ready for Production and final build / minification. Here, Webpack is
  defined and streamed into the Gulp process.

  We first define each task as a function that accepts a callback. This allows us leverage gulp's
  lastRun feature which will only recompile files that have changed since the last time the task was run.
  This greatly increases performance when running gulp watch with many files.
\*------------------------------------*/
function blockScriptsWatch() {
  return src(paths.blocks.scripts.src, {
    since: lastRun(blockScriptsWatch),
  })
    .pipe(named(renameBlockScripts))
    .pipe(webpack(webpackConfig), webpackCompiler)
    .on('error', function (err) {
      this.emit('end'); // Don't stop the rest of the task
    })
    .pipe(dest(paths.blocks.scripts.dest));
}
function blockScriptsBuild() {
  return src(paths.blocks.scripts.src)
    .pipe(named(renameBlockScripts))
    .pipe(webpack(webpackConfig), webpackCompiler)
    .pipe(ignore.exclude(['**/*.map']))
    .pipe(uglify())
    .pipe(dest(paths.blocks.scripts.dest));
}

/*------------------------------------*\
  08 -  Editor Styles
  Define both compilation of SASS files during development and also when ready for Production and final build / minification.
\*------------------------------------*/
function editor() {
  // Clean asset
  src('./editor-style.css', {
    read: false,
    allowEmpty: true,
  }).pipe(clean());

  // Compile styles
  return src(paths.editor.src)
    .pipe(sass())
    .on('error', sass.logError)
    .pipe(concat('editor-style.css'))
    .pipe(dest('.'))
    .pipe(browserSync.stream());
}

/*------------------------------------*\
  09 - Exports
  Define both the developmental, "Watch" and final production, "Build"
  processes for compiling files. The final production, "Build" process includes
  minified files.

  The BrowserSync Proxy address is determined by creating a custom version of a .env file, from the .env-example file.
  Here you will specify the exact local address of your website.
\*------------------------------------*/
exports.watch = () => {
  console.log('You are currently in development watch mode.');
  const watchOptions = {
    // Ensure all files are built when the task starts.
    ignoreInitial: false,
  };
  browserSync.init({
    proxy: process.env.BS_PROXY || 'http://appserver',
    browser: process.env.BS_BROWSER || 'google chrome',
    socket: {
      domain: 'https://bs.wordpress-starter.lndo.site', // The node proxy domain you defined in .lando.yaml. Must be https?
      port: 80, // NOT the 3000 you might expect.
    },
    open: false,
    logConnections: true,
  });

  // Base Styles & Scripts
  watch(paths.base.styles.src, watchOptions, series(baseStylesWatch));
  watch(paths.base.scripts.src, watchOptions, series(baseScriptsWatch));

  // Block Styles & Scripts
  watch(paths.blocks.styles.src, watchOptions, series(blockStylesWatch));
  watch(paths.blocks.scripts.src, watchOptions, series(blockScriptsWatch));

  // Editor Styles
  watch(paths.editor.src, series(editor));
};

exports.build = (done) => {
  console.log('You are building for production.');
  // Base Styles & Scripts
  parallel(baseStylesBuild)(done);
  parallel(baseScriptsBuild)(done);

  // Block Styles & Scripts
  parallel(blockStylesBuild)(done);
  parallel(blockScriptsBuild)(done);

  // Editor Styles
  parallel(editor)(done);
};
