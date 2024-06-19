/**
 * Custom Animation Functions for use sitewide
 */

jQuery( document ).ready(function($) {
    checkViewportForAnimations();

    $(window).scroll(function() {
        $('.animate-fade-in:not(.css-animate-fade-in)').each(function(){
            fadeInAnimation(this);
        });

        $('.animate-fade-in-slide-up:not(.css-animate-fade-in-slide-up)').each(function(){
            fadeInSlideUpAnimation(this);
        });

        $('.animate-slide-up:not(.css-animate-slide-up)').each(function(){
            slideUpAnimation(this);
        });
    });
    
    function checkViewportForAnimations() {
        $('.animate-slide-up:not(.css-animate-slide-up)').each(function(){
            slideUpAnimation(this);
        });

        $('.animate-fade-in-slide-up:not(.css-animate-fade-in-slide-up)').each(function(){
            fadeInSlideUpAnimation(this);
        });

        $('.animate-fade-in:not(.css-animate-fade-in)').each(function(){
            fadeInAnimation(this);
        });
    }

    function fadeInAnimation(element) {
        var element_position = $(element).offset().top;
        var element_height_offset = ($(element).height() * .5);
        var bottom_of_viewport = $(window).scrollTop() + window.innerHeight;

        if ((element_position - element_height_offset) < bottom_of_viewport) {
            $(element).removeClass('animate-fade-in').addClass('css-animate-fade-in');
        }
    }

    function fadeInSlideUpAnimation(element) {
        var element_position = $(element).offset().top;
        var element_height_offset = ($(element).height() * .5);
        var bottom_of_viewport = $(window).scrollTop() + window.innerHeight;

        if ((element_position - element_height_offset) < bottom_of_viewport)  {
            $(element).removeClass('animate-fade-in-slide-up').addClass('css-animate-fade-in-slide-up');
        }
    }

    function slideUpAnimation(element) {
        var element_position = $(element).offset().top;
        var element_height_offset = ($(element).height() * .5);
        var bottom_of_viewport = $(window).scrollTop() + window.innerHeight;

        if ((element_position - element_height_offset) < bottom_of_viewport)  {
            $(element).removeClass('animate-slide-up').addClass('css-animate-slide-up');
        }
    }
});