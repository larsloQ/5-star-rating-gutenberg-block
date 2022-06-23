/* just watching our build files and trigger a page reload / refresh */
const gulp = require('gulp');
const browserSync = require( 'browser-sync' );

/* --adjust-- set your local dev url */
const LOCALSERVER = 'http://localhost/github/5-star-rating-block';
const PORT = 1984;

// Start a server with BrowserSync to preview the site in
gulp.task( 'browser-sync', function(done) {
  browserSync.create();
  browserSync.init( {
    proxy: LOCALSERVER, 
    port: PORT,
    open:false
  } );
  done();
} );

// Watch for changes to static assets, pages, Sass, and JavaScript
gulp.task( 'watch', function(done) {
  gulp.watch( './build/js/*.js' ).on( 'change', browserSync.reload );
  gulp.watch( './build/css/*.css' ).on( 'change', browserSync.reload );
  done();
} );

gulp.task( 'default', gulp.parallel( "browser-sync", "watch" ) );
