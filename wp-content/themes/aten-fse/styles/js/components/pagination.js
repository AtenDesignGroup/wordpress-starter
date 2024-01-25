/**
 * Custom JS for default WP Pagination
 */

jQuery( document ).ready(function($) {
    $('.wp-block-query-pagination').each(function(){
        $(this).find('.wp-block-query-pagination-previous').html('<span class="pagination-icon">chevron_left</span><span class="a11y-visible"> Previous Page</span>');
        $(this).find('.wp-block-query-pagination-next').html('<span class="pagination-icon">chevron_right</span><span class="a11y-visible"> Next Page</span>');
        $(this).find('.wp-block-query-pagination-numbers').wrapInner('<ol class="pagination-list">');
        $(this).find('.pagination-list > *').wrap('<li class="pagination-item">');
        $(this).find('span.page-numbers.current').convertElement('a');
        $(this).find('a.page-numbers.current').attr("href", '#');
    });
});