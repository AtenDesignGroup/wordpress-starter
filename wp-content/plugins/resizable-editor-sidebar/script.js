jQuery(window).ready(function(){
    setTimeout(function(){
        jQuery('.interface-interface-skeleton__sidebar').width(localStorage.getItem('toast_rs_personal_sidebar_width'))
        jQuery('.interface-interface-skeleton__sidebar').resizable({
            handles: 'w',
            resize: function(event, ui) {
                jQuery(this).css({'left': 0});
                localStorage.setItem('toast_rs_personal_sidebar_width', jQuery(this).width());
           }
        });
    }, 500)

})