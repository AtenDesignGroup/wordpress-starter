jQuery(function($){
    // ------------- TAB HANDLING ---------------
    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');

    if(hash != ''){
        hash = parseInt(hash);
        $('.tabs a[tabid=' + Math.floor( hash / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + hash + ']').trigger('click');
    } else {
        $('.tabs a[tabid=1]').trigger('click');
    }

    $('.tabs a').on('click', function(){
        location.hash = $(this).attr('tabid');
    });
    // ------------------------------------------

    // -------- Individual Priorities -----------
    $("#p_asp_submit").on('click', function(e){
        e.preventDefault();
        var data = {
            action: 'ajaxsearchpro_priorities',
            'asp_priorities_request_nonce': $('#asp_priorities_request_nonce').val(),
            options: $("#asp_priorities").serialize(),
            ptask: "get"
        };
        $('#p_asp_loader').css('display', 'block');
        $('#p_asp_results').fadeOut(10);
        var post = $.post(ASP_PTS.ajax_url, data, function (response) {
            response = response.replace(/^\s*[\r\n]/gm, "");
            response = response.match(/!!PASPSTART!!(.*[\s\S]*)!!PASPEND!!/)[1];
            response = JSON.parse(response);
            var html = '';

            $.each(response, function(k, v){
                var input = "<input type='text' name='priority[" + v.id + "]' style='width:40px;' value='100' />";
                var old_priority = "<input type='hidden' name='old_priority[" + v.id + "]' value='100'/>";
                if (typeof (v.priority) != 'undefined') {
                    input = "<input type='text' style='width:40px;' name='priority[" + v.id + "]' value='" + v.priority + "'/>";
                    old_priority = "<input type='hidden' name='old_priority[" + v.id + "]' value='" + v.priority + "'/>";
                }
                html += "<div class='p_asp_row'><p class='p_asp_title'>["+ v.id +"] " + v.title + "</p><p class='p_asp_priority'><label>" + msg('msg_pri') + "</label>" + input + old_priority + "</p><p class='p_asp_date'>" + v.date + "</p><p class='p_asp_author'>" + v.author + "</p></div>";
            });
            if (html == '') {
                $('#p_asp_loader').css('display', 'none');
                $('#p_asp_results').html('<p style="text-align:center;">No results!</p>');
                return true;
            }
            html += "<input type='hidden' name='p_blogid' value='"+$('select[name="p_asp_blog"]').val()+"'>";
            $('#p_asp_results').html("<div class='p_row_header_footer'><p>" + msg('msg_pda') + "</p><input type='submit' class='p_asp_save' value='" + msg('msg_sav') + "'></div><form name='asp_priorities_list' id='asp_priorities_list' method='post'>" + html + "</form><div class='p_row_header_footer'><p>" + msg('msg_pda') + "</p><input type='submit' class='p_asp_save' value='" + msg('msg_sav') + "'></div>");
            $('#p_asp_loader').css('display', 'none');
            $('#p_asp_results').fadeIn(150);
        }, "text");
    });

    $('#p_asp_results').on('click', '.p_asp_save', function(e){
        e.preventDefault();
        var $this = $(this);
        var data = {
            action: 'ajaxsearchpro_priorities',
            'asp_priorities_request_nonce': $('#asp_priorities_request_nonce').val(),
            options: $("#asp_priorities_list").serialize(),
            ptask: "set"
        };
        $this.prop('disabled', true);
        $('#p_asp_results').fadeOut(10);
        $('#p_asp_loader').css('display', 'block');
        var post = $.post(ASP_PTS.ajax_url, data, function (response) {
            response = response.replace(/^\s*[\r\n]/gm, "");
            response = response.match(/!!PSASPSTART!!(.*[\s\S]*)!!PSASPEND!!/)[1];
            response = JSON.parse(response);
            $this.prop('disabled', false);
            $('#p_asp_loader').css('display', 'none');
            $("#p_asp_submit").trigger('click');
        }, "text");
    });

    // ------------------------------------------- ETC -----------------------------------------------------------------
    function msg(k) {
        return typeof ASP_PTS[k] != 'undefined' ? ASP_PTS[k] : '';
    }
});