/**
 * Custom JS for default WP Pagination
 */

jQuery( document ).ready(function($) {
    $('nav.pagination').each(function(){
        $(this).find('.page-numbers:not(".prev"):not(".next")').wrapAll('<ol class="pagination-list">');
        $(this).find('.pagination-list > *').wrap('<li class="pagination-item">');
        $(this).find('span.page-numbers.current').convertElement('a');
        $(this).find('a.page-numbers.current').attr("href", '#');
    });
});