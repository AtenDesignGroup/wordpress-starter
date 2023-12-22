document.addEventListener( 'DOMContentLoaded', function() {
    // Homepage Hero carousel
    var homepage_slider_container = document.getElementById('homepage-image-carousel');
    if (homepage_slider_container != null) {

        var homepage_slider = new Splide( '#homepage-image-carousel', {
            arrows : false,
            autoplay : true,
            drag : false,
            interval : 3000,
            keyboard : true,
            pagination : false,
            rewind : true,
            speed : 1000,
            type : 'fade',
        } ).mount();

        var homepage_toggleButton = homepage_slider.root.querySelector( '.pause-toggle-button' );

        homepage_slider.on( 'autoplay:play', function () {
            homepage_toggleButton.setAttribute( 'aria-label', 'Pause autoplay' );
            homepage_toggleButton.textContent = 'pause_circle';
        } );
        
        homepage_slider.on( 'autoplay:pause', function () {
            homepage_toggleButton.setAttribute( 'aria-label', 'Start autoplay' );
            homepage_toggleButton.textContent = 'play_circle';
        } );

        homepage_toggleButton.addEventListener( 'click', function () {
            var homepage_autoplay = homepage_slider.Components.Autoplay;
        
            if ( homepage_autoplay.isPaused() ) {
                homepage_autoplay.play();
            } else {
                homepage_autoplay.pause();
            }
        } );
    }

    // Rotating Banner carousel
    var rotating_banner_container = document.getElementById('rotating-banner');
    if (rotating_banner_container != null) {
        var rotating_banner = new Splide( '#rotating-banner', {
            arrows : true,
            autoplay : false,
            keyboard : true,
            mediaQuery: 'min',
            pagination : false,
            perMove : 1,
            perPage : 1,
            speed : 1000,
            type : 'loop',
            breakpoints: {
                1200: {
                    perPage: 2
                },
                1400: {
                    gap: '.5rem'
                }
          }
        } ).mount();
    }

} );