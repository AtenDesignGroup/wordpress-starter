/**
 * Custom JS for the Image Gallery Block
 */

jQuery( document ).ready(function($) {
    $('.tobii-image').each(function(){
        $(this).find('figcaption').wrapInner('<span class="lightbox-caption" />');
        var imageSrc = $(this).find('img').attr('data-src');
        var downloadBtn = $('.img-download-link[href="' + imageSrc + '"]');
        $(downloadBtn).clone().insertAfter($(this).find('.lightbox-caption'));
    });
});